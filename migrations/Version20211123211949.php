<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211123211949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE task CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD lastname VARCHAR(100) NOT NULL, ADD firstname VARCHAR(100) NOT NULL, ADD phone_number VARCHAR(10) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP lastname, DROP firstname, DROP phone_number, DROP created_at, DROP updated_at');
    }
}
