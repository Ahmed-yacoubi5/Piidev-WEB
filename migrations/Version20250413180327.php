<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413180327 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE art_like DROP FOREIGN KEY FK_A543362C7294869C');
        $this->addSql('ALTER TABLE art_like DROP FOREIGN KEY FK_A543362CA76ED395');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D7294869C');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE model3_d DROP FOREIGN KEY FK_B1A414D671F7E88B');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F74B89032C');
        $this->addSql('ALTER TABLE reaction DROP FOREIGN KEY FK_A4D707F7A76ED395');
        $this->addSql('DROP TABLE art_like');
        $this->addSql('DROP TABLE bad_word');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE model3_d');
        $this->addSql('DROP TABLE reaction');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66825396CB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66825396CB FOREIGN KEY (galerie_id) REFERENCES galerie (id)');
        $this->addSql('ALTER TABLE comment DROP gif_url');
        $this->addSql('ALTER TABLE user ADD photo_de_profile VARCHAR(255) DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, DROP avatar_url, DROP reset_token_expires_at, DROP reset_token, DROP last_login_at, CHANGE email email VARCHAR(255) NOT NULL, CHANGE numtlf numtlf VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE art_like (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, liked_at DATETIME NOT NULL, INDEX IDX_A543362CA76ED395 (user_id), INDEX IDX_A543362C7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE bad_word (id INT AUTO_INCREMENT NOT NULL, word VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, replacement VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, article_id INT NOT NULL, numero VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_commande DATETIME NOT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, INDEX IDX_6EEAA67DA76ED395 (user_id), INDEX IDX_6EEAA67D7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE model3_d (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, file_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_B1A414D671F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reaction (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, type VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, emoji VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, INDEX IDX_A4D707F7A76ED395 (user_id), INDEX IDX_A4D707F74B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE art_like ADD CONSTRAINT FK_A543362C7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE art_like ADD CONSTRAINT FK_A543362CA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE model3_d ADD CONSTRAINT FK_B1A414D671F7E88B FOREIGN KEY (event_id) REFERENCES event (idevent)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F74B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE reaction ADD CONSTRAINT FK_A4D707F7A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66825396CB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66825396CB FOREIGN KEY (galerie_id) REFERENCES galerie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD gif_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` ADD reset_token VARCHAR(255) DEFAULT NULL, ADD last_login_at DATETIME DEFAULT NULL, CHANGE email email VARCHAR(180) NOT NULL, CHANGE numtlf numtlf VARCHAR(255) DEFAULT NULL, CHANGE photo_de_profile avatar_url VARCHAR(255) DEFAULT NULL, CHANGE updated_at reset_token_expires_at DATETIME DEFAULT NULL');
    }
}
