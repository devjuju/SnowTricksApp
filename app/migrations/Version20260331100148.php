<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260331100148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE videos DROP FOREIGN KEY `FK_29AA6432B281BE2E`');
        $this->addSql('ALTER TABLE videos ADD url LONGTEXT DEFAULT NULL, CHANGE youtube_id youtube_id VARCHAR(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE videos ADD CONSTRAINT FK_29AA6432B281BE2E FOREIGN KEY (trick_id) REFERENCES tricks (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE videos DROP FOREIGN KEY FK_29AA6432B281BE2E');
        $this->addSql('ALTER TABLE videos DROP url, CHANGE youtube_id youtube_id VARCHAR(11) NOT NULL');
        $this->addSql('ALTER TABLE videos ADD CONSTRAINT `FK_29AA6432B281BE2E` FOREIGN KEY (trick_id) REFERENCES tricks (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
