#!/bin/bash
set -e

echo "🚀 Préparation du serveur pour le déploiement automatisé (Debian 13)"
echo "=========================================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "❌ Ce script doit être exécuté en tant que root ou avec sudo"
    exit 1
fi

DEPLOY_USER=${DEPLOY_USER:-}
DEPLOY_PATH=${DEPLOY_PATH:-}
SSH_PORT=${SSH_PORT:-}

if [ -z "$DEPLOY_USER" ]; then
    echo "❌ La variable DEPLOY_USER doit être définie"
    echo "   Exemple: DEPLOY_USER=debian DEPLOY_PATH=/home/debian/app SSH_PORT=51845 ./setup-server.sh"
    exit 1
fi

if [ -z "$DEPLOY_PATH" ]; then
    echo "❌ La variable DEPLOY_PATH doit être définie"
    echo "   Exemple: DEPLOY_USER=debian DEPLOY_PATH=/home/debian/app SSH_PORT=51845 ./setup-server.sh"
    exit 1
fi

if [ -z "$SSH_PORT" ]; then
    echo "❌ La variable SSH_PORT doit être définie"
    echo "   Exemple: DEPLOY_USER=debian DEPLOY_PATH=/home/debian/app SSH_PORT=51845 ./setup-server.sh"
    exit 1
fi

echo ""
echo "Configuration:"
echo "  - Utilisateur de déploiement: $DEPLOY_USER"
echo "  - Chemin de déploiement: $DEPLOY_PATH"
echo "  - Port SSH: $SSH_PORT"
echo ""

read -p "Continuer avec cette configuration ? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Installation annulée"
    exit 1
fi

echo ""
echo "📦 Mise à jour du système..."
apt update && apt upgrade -y

echo ""
echo "🐳 Installation de Docker..."

if command -v docker &> /dev/null; then
    echo "✅ Docker est déjà installé"
    docker --version
else
    apt install -y ca-certificates curl gnupg lsb-release

    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg

    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
      $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

    apt update
    apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    systemctl enable docker
    systemctl start docker

    echo "✅ Docker installé avec succès"
    docker --version
fi

echo ""
echo "🔧 Configuration réseau Docker (Debian 13 / nftables)..."

# Modules utiles (si dispo)
modprobe br_netfilter || true

# Sysctl utiles pour Docker + ponts
cat > /etc/sysctl.d/99-docker.conf << 'EOF'
net.ipv4.ip_forward=1
net.bridge.bridge-nf-call-iptables=1
net.bridge.bridge-nf-call-ip6tables=1
EOF

sysctl --system

# Configuration explicite de Docker (évite les surprises)
mkdir -p /etc/docker
cat > /etc/docker/daemon.json << 'JSON'
{
  "iptables": true,
  "icc": true
}
JSON

systemctl restart docker

echo "✅ Réseau Docker configuré (iptables=true, icc=true)"

echo ""
echo "👤 Configuration de l'utilisateur de déploiement..."

if id "$DEPLOY_USER" &>/dev/null; then
    echo "✅ L'utilisateur $DEPLOY_USER existe déjà"
else
    useradd -m -s /bin/bash "$DEPLOY_USER"
    echo "✅ Utilisateur $DEPLOY_USER créé"
fi

usermod -aG docker "$DEPLOY_USER"
echo "✅ Utilisateur $DEPLOY_USER ajouté au groupe docker"

echo ""
echo "📥 Installation de Git..."

if command -v git &> /dev/null; then
    echo "✅ Git est déjà installé"
    git --version
else
    apt install -y git
    echo "✅ Git installé avec succès"
    git --version
fi

echo ""
echo "🔄 Configuration des mises à jour automatiques de sécurité..."

apt install -y unattended-upgrades apt-listchanges

cat > /etc/apt/apt.conf.d/50unattended-upgrades << 'EOF'
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
};

Unattended-Upgrade::Package-Blacklist {
    // "docker-ce";
    // "docker-ce-cli";
};

Unattended-Upgrade::Remove-Unused-Dependencies "true";
Unattended-Upgrade::Remove-Unused-Kernel-Packages "true";

Unattended-Upgrade::Automatic-Reboot "false";
Unattended-Upgrade::Automatic-Reboot-Time "02:00";
EOF

cat > /etc/apt/apt.conf.d/20auto-upgrades << 'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
EOF

systemctl enable unattended-upgrades
systemctl start unattended-upgrades

echo "✅ Mises à jour automatiques de sécurité configurées"

echo ""
echo "📁 Création du répertoire de déploiement..."

mkdir -p "$DEPLOY_PATH"
chown -R "$DEPLOY_USER":"$DEPLOY_USER" "$DEPLOY_PATH"

echo "✅ Répertoire créé: $DEPLOY_PATH"

echo ""
echo "🔥 Configuration du firewall (nftables, compatible Docker)..."

# nftables nat/filter peut être géré par iptables-nft/Docker : on ne le casse pas.
# On pose un pare-feu simple sur inet/filter pour INPUT.
apt install -y nftables

cat > /etc/nftables.conf << EOF
#!/usr/sbin/nft -f

flush ruleset

table inet filter {
  chain input {
    type filter hook input priority 0; policy drop;

    # Loopback
    iifname "lo" accept

    # Connexions établies
    ct state established,related accept

    # SSH
    tcp dport $SSH_PORT accept

    # HTTP/HTTPS (Caddy)
    tcp dport 80 accept
    tcp dport 443 accept

    # ICMP (ping)
    ip protocol icmp icmp type echo-request accept
    ip6 nexthdr icmpv6 icmpv6 type { echo-request, nd-neighbor-solicit, nd-neighbor-advert, nd-router-solicit, nd-router-advert } accept

    # (optionnel) log des paquets droppés (décommenter si besoin)
    # log prefix "nft-in-drop " flags all counter drop
  }

  chain forward {
    type filter hook forward priority 0; policy accept;
  }

  chain output {
    type filter hook output priority 0; policy accept;
  }
}
EOF

systemctl enable nftables
systemctl restart nftables

echo "✅ Firewall nftables configuré"
echo "   (INPUT=drop avec exceptions, FORWARD/OUTPUT=accept pour compat Docker)"

echo ""
echo "🛡️  Installation de Fail2ban..."

if command -v fail2ban-client &> /dev/null; then
    echo "✅ Fail2ban est déjà installé"
    fail2ban-client version
else
    apt install -y fail2ban
    echo "✅ Fail2ban installé avec succès"
fi

cat > /etc/fail2ban/jail.local << EOF
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5
ignoreip = 127.0.0.1/8 ::1

[sshd]
enabled = true
port = $SSH_PORT
logpath = /var/log/auth.log
maxretry = 3
bantime = 7200
findtime = 600
EOF

systemctl enable fail2ban
systemctl restart fail2ban

sleep 3

echo "✅ Fail2ban configuré et démarré"
fail2ban-client status sshd || echo "⚠️  Fail2ban démarre, vérifiez avec: sudo fail2ban-client status sshd"

echo ""
echo "📊 Configuration de la rotation des logs..."

cat > /etc/logrotate.d/appli-web-ticket << 'EOF'
/var/lib/docker/containers/*/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    missingok
    copytruncate
    su root root
    maxsize 100M
}
EOF

echo "✅ Rotation des logs configurée"

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║  ✅ Serveur prêt pour le déploiement automatisé !          ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

echo "📊 Informations système:"
echo "   - OS: $(lsb_release -d | cut -f2)"
echo "   - Docker: $(docker --version)"
echo "   - Docker Compose: $(docker compose version)"
echo "   - Git: $(git --version)"
echo "   - Fail2ban: $(fail2ban-client version 2>/dev/null || echo 'installé')"
echo "   - Port SSH: $SSH_PORT"
echo "   - MAJ auto: ✅ Activées (sécurité uniquement)"
echo "   - IP publique: $(curl -s ifconfig.me || echo 'N/A')"
echo ""

echo "🔒 Sécurité:"
echo "   - Firewall: ✅ nftables (INPUT drop + exceptions, forward accept)"
echo "   - Fail2ban: ✅ Actif (protection SSH)"
echo "   - Port SSH: $SSH_PORT (non-standard)"
echo "   - MAJ auto sécurité: ✅ Quotidiennes (sans reboot)"
echo ""

echo "📝 Commandes utiles:"
echo "   - Vérifier Fail2ban: fail2ban-client status sshd"
echo "   - Débannir une IP: fail2ban-client set sshd unbanip <IP>"
echo "   - Voir règles nft: nft list ruleset"
echo "   - Voir Docker info: docker info"
echo "   - Logs Fail2ban: tail -f /var/log/fail2ban.log"
echo ""
echo "🎉 Installation terminée avec succès !"
