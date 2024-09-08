<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240908113739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610CBC66766 FOREIGN KEY (user_file_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invoice CHANGE user_invoice_id user_invoice_id INT NOT NULL');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448EEE3711 FOREIGN KEY (user_invoice_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744C61FC64C FOREIGN KEY (transaction_invoice_id) REFERENCES transaction (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D144451456 FOREIGN KEY (user_transaction_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610CBC66766');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517448EEE3711');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744C61FC64C');
        $this->addSql('ALTER TABLE invoice CHANGE user_invoice_id user_invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D144451456');
    }
}
