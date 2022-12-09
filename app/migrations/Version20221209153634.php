<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221209153634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id UUID NOT NULL, micro_post_id UUID NOT NULL, content VARCHAR(300) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9474526C11E37CEA ON comment (micro_post_id)');
        $this->addSql('COMMENT ON COLUMN comment.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN comment.micro_post_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C11E37CEA FOREIGN KEY (micro_post_id) REFERENCES micro_post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C11E37CEA');
        $this->addSql('DROP TABLE comment');
    }
}
