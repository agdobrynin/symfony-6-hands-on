<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221209144503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_profile ADD to_user_id UUID NOT NULL');
        $this->addSql('COMMENT ON COLUMN user_profile.to_user_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE user_profile ADD CONSTRAINT FK_D95AB40529F6EE60 FOREIGN KEY (to_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D95AB40529F6EE60 ON user_profile (to_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_profile DROP CONSTRAINT FK_D95AB40529F6EE60');
        $this->addSql('DROP INDEX UNIQ_D95AB40529F6EE60');
        $this->addSql('ALTER TABLE user_profile DROP to_user_id');
    }
}
