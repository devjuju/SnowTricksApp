<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329101052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. ajouter colonne
        $this->addSql('ALTER TABLE videos ADD youtube_id VARCHAR(11) DEFAULT NULL');

        // 2. migrer les données existantes
        $this->addSql("
        UPDATE videos
        SET youtube_id = SUBSTRING_INDEX(SUBSTRING_INDEX(url, 'v=', -1), '&', 1)
        WHERE url LIKE '%youtube.com/watch%'
    ");

        $this->addSql("
        UPDATE videos
        SET youtube_id = SUBSTRING_INDEX(url, '/', -1)
        WHERE url LIKE '%youtu.be/%'
    ");

        // 3. rendre NOT NULL si tu veux
        $this->addSql('ALTER TABLE videos MODIFY youtube_id VARCHAR(11) NOT NULL');

        // 4. supprimer ancienne colonne
        $this->addSql('ALTER TABLE videos DROP url');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE videos ADD url VARCHAR(255) DEFAULT NULL, DROP youtube_id');
    }
}
