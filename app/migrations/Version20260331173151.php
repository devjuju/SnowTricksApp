<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260331173151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tricks ADD featured_image_hash VARCHAR(64) DEFAULT NULL, DROP featured_image');
        $this->addSql('CREATE UNIQUE INDEX uniq_featured_image_hash ON tricks (featured_image_hash)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_featured_image_hash ON tricks');
        $this->addSql('ALTER TABLE tricks ADD featured_image VARCHAR(255) DEFAULT NULL, DROP featured_image_hash');
    }
}
