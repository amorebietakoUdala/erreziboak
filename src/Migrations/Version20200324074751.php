<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200324074751 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, timestamp DATETIME NOT NULL, reference_number VARCHAR(12) NOT NULL, reference_number_dc VARCHAR(12) DEFAULT NULL, suffix VARCHAR(3) NOT NULL, quantity NUMERIC(6, 2) NOT NULL, registered_payment_id VARCHAR(42) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, status_message VARCHAR(255) DEFAULT NULL, nrc VARCHAR(255) DEFAULT NULL, operation_number VARCHAR(255) DEFAULT NULL, entity VARCHAR(255) DEFAULT NULL, office VARCHAR(255) DEFAULT NULL, payment_date VARCHAR(255) DEFAULT NULL, payment_hour VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, surname_1 VARCHAR(255) DEFAULT NULL, surname_2 VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, nif VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, territory VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, response VARCHAR(4000) DEFAULT NULL, source INT NOT NULL, UNIQUE INDEX UNIQ_6D28840DC5BF6507 (registered_payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE payment');
    }
}
