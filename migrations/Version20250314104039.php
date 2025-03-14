<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314104039 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE receipts_file ADD uploaded_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE receipts_file ADD CONSTRAINT FK_454C3776A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_454C3776A2B28FE8 ON receipts_file (uploaded_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE receipts_file DROP FOREIGN KEY FK_454C3776A2B28FE8');
        $this->addSql('DROP INDEX IDX_454C3776A2B28FE8 ON receipts_file');
        $this->addSql('ALTER TABLE receipts_file DROP uploaded_by_id');
    }
}
