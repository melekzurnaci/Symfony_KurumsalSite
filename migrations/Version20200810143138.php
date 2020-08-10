<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200810143138 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE setting (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(200) DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, company VARCHAR(150) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) NOT NULL, fax VARCHAR(20) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, smtpserver VARCHAR(20) DEFAULT NULL, smtpemail VARCHAR(75) DEFAULT NULL, smtppassword VARCHAR(20) DEFAULT NULL, smtpport VARCHAR(5) DEFAULT NULL, facebook VARCHAR(30) DEFAULT NULL, instagram VARCHAR(30) DEFAULT NULL, twitter VARCHAR(30) DEFAULT NULL, aboutus LONGTEXT DEFAULT NULL, contact LONGTEXT DEFAULT NULL, reference LONGTEXT DEFAULT NULL, status VARCHAR(6) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE setting');
    }
}
