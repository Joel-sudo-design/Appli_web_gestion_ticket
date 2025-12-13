#!/bin/bash

# ==============================================
# Script de préparation du serveur de production
# ==============================================
# Ce script doit être exécuté UNE FOIS sur le serveur de production
# avant le premier déploiement via GitHub Actions

set -e

echo "🚀 Préparation du serveur pour le déploiement automatisé"
echo "=========================================================="
echo ""

# Vérifier si on est root ou avec sudo
if [ "$EUID" -ne 0 ]; then
    echo "❌ Ce script doit être exécuté en tant que root ou avec sudo"
    exit 1
fi

# Variables
DEPLOY_USER=${DEPLOY_USER:-}
DEPLOY_PATH=${DEPLOY_PATH:-}
SSH_PORT=${SSH_PORT:-}

# Vérifier que le port SSH est défini
if [ -z "$SSH_PORT" ]; then
    echo "❌ La variable SSH_PORT doit être définie"
    echo "   Exemple: SSH_PORT=2222 ./setup-server.sh"
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

# ==============================================
# 1. Mise à jour du système
# ==============================================
echo ""
echo "📦 Mise à jour du système..."
apt update && apt upgrade -y

# ==============================================
# 2. Installation de Docker
# ==============================================
echo ""
echo "🐳 Installation de Docker..."

if command -v docker &> /dev/null; then
    echo "✅ Docker est déjà installé"
    docker --version
else
    # Installer les dépendances
    apt install -y ca-certificates curl gnupg lsb-release

    # Ajouter la clé GPG officielle de Docker
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    chmod a+r /etc/apt/keyrings/docker.gpg

    # Ajouter le repository Docker pour Debian
    echo \
      "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
      $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null

    # Installer Docker
    apt update
    apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

    # Démarrer Docker
    systemctl enable docker
    systemctl start docker

    echo "✅ Docker installé avec succès"
    docker --version
fi

# ==============================================
# 3. Création de l'utilisateur de déploiement
# ==============================================
echo ""
echo "👤 Configuration de l'utilisateur de déploiement..."

if id "$DEPLOY_USER" &>/dev/null; then
    echo "✅ L'utilisateur $DEPLOY_USER existe déjà"
else
    # Créer l'utilisateur
    useradd -m -s /bin/bash $DEPLOY_USER
    echo "✅ Utilisateur $DEPLOY_USER créé"
fi

# Ajouter l'utilisateur au groupe docker
usermod -aG docker $DEPLOY_USER
echo "✅ Utilisateur $DEPLOY_USER ajouté au groupe docker"

# ==============================================
# 4. Installation de Git
# ==============================================
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

# ==============================================
# 5. Configuration des mises à jour automatiques
# ==============================================
echo ""
echo "🔄 Configuration des mises à jour automatiques de sécurité..."

apt install -y unattended-upgrades apt-listchanges

# Configurer unattended-upgrades
cat > /etc/apt/apt.conf.d/50unattended-upgrades << 'EOF'
// Configuration des mises à jour automatiques
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
};

// Liste des paquets à ne pas mettre à jour automatiquement
Unattended-Upgrade::Package-Blacklist {
    // "docker-ce";
    // "docker-ce-cli";
};

// Supprimer les dépendances inutiles
Unattended-Upgrade::Remove-Unused-Dependencies "true";
Unattended-Upgrade::Remove-Unused-Kernel-Packages "true";

// Redémarrage automatique si nécessaire (DÉSACTIVÉ par défaut)
Unattended-Upgrade::Automatic-Reboot "false";

// Si reboot activé, à quelle heure (2h du matin)
Unattended-Upgrade::Automatic-Reboot-Time "02:00";

// Notifications par email (décommenter et configurer si besoin)
// Unattended-Upgrade::Mail "admin@example.com";
// Unattended-Upgrade::MailReport "on-change";
EOF

# Activer les mises à jour automatiques
cat > /etc/apt/apt.conf.d/20auto-upgrades << 'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
EOF

# Activer et démarrer le service
systemctl enable unattended-upgrades
systemctl start unattended-upgrades

echo "✅ Mises à jour automatiques de sécurité configurées"
echo "   - Vérification quotidienne des patchs de sécurité"
echo "   - Installation automatique (SANS redémarrage auto)"
echo "   - Nettoyage des anciens paquets après 7 jours"

# ==============================================
# 6. Création du répertoire de déploiement
# ==============================================
echo ""
echo "📁 Création du répertoire de déploiement..."

# Créer le répertoire
mkdir -p $DEPLOY_PATH
chown -R $DEPLOY_USER:$DEPLOY_USER $DEPLOY_PATH

echo "✅ Répertoire créé: $DEPLOY_PATH"

# ==============================================
# 7. Configuration du firewall (iptables pour Debian)
# ==============================================
echo ""
echo "🔥 Configuration du firewall..."

# Installer iptables-persistent pour Debian
apt install -y iptables iptables-persistent

# Configuration des règles iptables
echo "Configuration des règles iptables..."

# Effacer les règles existantes
iptables -F
iptables -X
iptables -t nat -F
iptables -t nat -X
iptables -t mangle -F
iptables -t mangle -X

# Politique par défaut
iptables -P INPUT DROP
iptables -P FORWARD DROP
iptables -P OUTPUT ACCEPT

# Autoriser le loopback
iptables -A INPUT -i lo -j ACCEPT

# Autoriser les connexions établies
iptables -A INPUT -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT

# Autoriser SSH sur le port personnalisé
iptables -A INPUT -p tcp --dport $SSH_PORT -j ACCEPT

# Autoriser HTTP et HTTPS
iptables -A INPUT -p tcp --dport 80 -j ACCEPT
iptables -A INPUT -p tcp --dport 443 -j ACCEPT

# Autoriser le ping (optionnel)
iptables -A INPUT -p icmp --icmp-type echo-request -j ACCEPT

# Sauvegarder les règles
netfilter-persistent save

echo "✅ Firewall configuré avec iptables"
iptables -L -v -n

# ==============================================
# 8. Installation et configuration de Fail2ban
# ==============================================
echo ""
echo "🛡️  Installation de Fail2ban..."

if command -v fail2ban-client &> /dev/null; then
    echo "✅ Fail2ban est déjà installé"
    fail2ban-client version
else
    apt install -y fail2ban
    echo "✅ Fail2ban installé avec succès"
fi

# Créer la configuration locale pour SSH
cat > /etc/fail2ban/jail.local << EOF
[DEFAULT]
# Ban des IPs pour 1 heure
bantime = 3600
# Fenêtre de temps pour compter les tentatives (10 minutes)
findtime = 600
# Nombre de tentatives avant ban
maxretry = 5
# Ignorer les IPs locales
ignoreip = 127.0.0.1/8 ::1

[sshd]
enabled = true
port = $SSH_PORT
logpath = /var/log/auth.log
maxretry = 3
bantime = 7200
findtime = 600
EOF

# Activer et démarrer Fail2ban
systemctl enable fail2ban
systemctl restart fail2ban

# Attendre que Fail2ban démarre
sleep 3

echo "✅ Fail2ban configuré et démarré"
fail2ban-client status sshd || echo "⚠️  Fail2ban démarre, vérifiez avec: sudo fail2ban-client status sshd"

# ==============================================
# 9. Configuration de logrotate
# ==============================================
echo ""
echo "📊 Configuration de la rotation des logs..."

cat > /etc/logrotate.d/appli-web-ticket << EOF
$DEPLOY_PATH/var/log/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    missingok
    create 0644 $DEPLOY_USER $DEPLOY_USER
}
EOF

echo "✅ Rotation des logs configurée"

# ==============================================
# Résumé
# ==============================================
echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║  ✅ Serveur prêt pour le déploiement automatisé !          ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Afficher les informations importantes
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
echo "   - Firewall: ✅ Configuré (iptables)"
echo "   - Fail2ban: ✅ Actif (protection SSH)"
echo "   - Port SSH: $SSH_PORT (non-standard)"
echo "   - MAJ auto sécurité: ✅ Quotidiennes (sans reboot)"
echo ""

echo "📝 Commandes utiles:"
echo "   - Vérifier Fail2ban: fail2ban-client status sshd"
echo "   - Débannir une IP: fail2ban-client set sshd unbanip <IP>"
echo "   - Voir les règles iptables: iptables -L -v -n"
echo "   - Logs Fail2ban: tail -f /var/log/fail2ban.log"
echo "   - Logs MAJ auto: cat /var/log/unattended-upgrades/unattended-upgrades.log"
echo "   - Forcer MAJ sécu: unattended-upgrade -d"
echo "   - Vérifier MAJ dispo: apt list --upgradable"
echo ""

echo "🎉 Installation terminée avec succès !"