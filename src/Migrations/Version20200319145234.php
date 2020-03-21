<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200319145234 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ticket CHANGE id_order_id id_order_id INT NOT NULL, CHANGE visitor_name visitor_name VARCHAR(32) NOT NULL, CHANGE visitor_birthday visitor_birthday DATE NOT NULL, CHANGE reduction reduction TINYINT(1) NOT NULL, CHANGE price price DOUBLE PRECISION NOT NULL, CHANGE country country VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE user ADD stripe_token VARCHAR(64) DEFAULT NULL, CHANGE order_code order_code CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE order_date order_date DATE NOT NULL, CHANGE number_tickets number_tickets INT NOT NULL, CHANGE client_name client_name VARCHAR(32) NOT NULL, CHANGE client_address client_address VARCHAR(128) NOT NULL, CHANGE client_country client_country VARCHAR(32) NOT NULL, CHANGE client_email client_email VARCHAR(64) NOT NULL, CHANGE visit_date visit_date DATE NOT NULL, CHANGE visit_duration visit_duration NUMERIC(2, 1) NOT NULL, CHANGE total_price total_price DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ticket CHANGE id_order_id id_order_id INT DEFAULT NULL, CHANGE visitor_name visitor_name VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE visitor_birthday visitor_birthday DATE DEFAULT NULL, CHANGE reduction reduction TINYINT(1) DEFAULT NULL, CHANGE price price DOUBLE PRECISION DEFAULT NULL, CHANGE country country VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user DROP stripe_token, CHANGE order_code order_code CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', CHANGE order_date order_date DATE DEFAULT NULL, CHANGE number_tickets number_tickets INT DEFAULT NULL, CHANGE client_name client_name VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE client_address client_address VARCHAR(128) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE client_country client_country VARCHAR(32) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE client_email client_email VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE visit_date visit_date DATE DEFAULT NULL, CHANGE visit_duration visit_duration NUMERIC(2, 1) DEFAULT NULL, CHANGE total_price total_price DOUBLE PRECISION DEFAULT NULL');
    }
}
