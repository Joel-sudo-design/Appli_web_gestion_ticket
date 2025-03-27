<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324110656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE open_ticket (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, INDEX IDX_7E88B5B6A76ED395 (user_id), INDEX IDX_7E88B5B6700047D2 (ticket_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1500) NOT NULL, date DATE NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_97A0ADA3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket_response (id INT AUTO_INCREMENT NOT NULL, ticket_id INT NOT NULL, user_id INT NOT NULL, contenu VARCHAR(2000) NOT NULL, INDEX IDX_BB12F77A700047D2 (ticket_id), INDEX IDX_BB12F77AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, api_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL_ADDRESS (email_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE open_ticket ADD CONSTRAINT FK_7E88B5B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE open_ticket ADD CONSTRAINT FK_7E88B5B6700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket_response ADD CONSTRAINT FK_BB12F77A700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE ticket_response ADD CONSTRAINT FK_BB12F77AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE open_ticket DROP FOREIGN KEY FK_7E88B5B6A76ED395');
        $this->addSql('ALTER TABLE open_ticket DROP FOREIGN KEY FK_7E88B5B6700047D2');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3A76ED395');
        $this->addSql('ALTER TABLE ticket_response DROP FOREIGN KEY FK_BB12F77A700047D2');
        $this->addSql('ALTER TABLE ticket_response DROP FOREIGN KEY FK_BB12F77AA76ED395');
        $this->addSql('DROP TABLE open_ticket');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE ticket_response');
        $this->addSql('DROP TABLE user');
    }
}
