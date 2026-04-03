<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329103033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. réparer les données existantes
        $this->addSql("
        UPDATE videos v
        JOIN tricks t ON v.trick_id = t.id
        SET v.user_id = t.user_id
        WHERE v.user_id IS NULL
    ");

        // 2. sécuriser (fallback si trick.user_id null)
        $this->addSql("
        UPDATE videos
        SET user_id = 1
        WHERE user_id IS NULL
    ");

        // 3. maintenant seulement on impose NOT NULL
        $this->addSql('ALTER TABLE videos CHANGE user_id user_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE videos CHANGE user_id user_id INT DEFAULT NULL');
    }
}
