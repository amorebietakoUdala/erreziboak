<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220407061955 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_titularity_check ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE account_titularity_check ADD CONSTRAINT FK_9BE48CFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9BE48CFA76ED395 ON account_titularity_check (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_titularity_check DROP FOREIGN KEY FK_9BE48CFA76ED395');
        $this->addSql('DROP INDEX IDX_9BE48CFA76ED395 ON account_titularity_check');
        $this->addSql('ALTER TABLE account_titularity_check DROP user_id');
    }
}
