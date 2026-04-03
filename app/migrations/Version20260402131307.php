<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260402131307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_image_per_trick ON images');
        $this->addSql('ALTER TABLE images CHANGE public_id public_id VARCHAR(32) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E01FBE6AB5B48B91 ON images (public_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_E01FBE6AB5B48B91 ON images');
        $this->addSql('ALTER TABLE images CHANGE public_id public_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_image_per_trick ON images (trick_id, picture)');
    }
}
