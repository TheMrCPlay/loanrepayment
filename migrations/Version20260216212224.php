<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216212224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE loan (id UUID NOT NULL, customer_id UUID NOT NULL, reference VARCHAR(16) NOT NULL, state VARCHAR(16) NOT NULL, amount_issued NUMERIC(18, 2) NOT NULL, amount_to_pay NUMERIC(18, 2) NOT NULL, paid_amount NUMERIC(18, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_loan_reference ON loan (reference)');
        $this->addSql('CREATE TABLE payment (id UUID NOT NULL, external_reference VARCHAR(64) NOT NULL, loan_reference VARCHAR(16) NOT NULL, amount NUMERIC(18, 2) NOT NULL, assigned_amount NUMERIC(18, 2) NOT NULL, state VARCHAR(32) NOT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_payment_external_ref ON payment (external_reference)');
        $this->addSql('CREATE TABLE payment_order (id UUID NOT NULL, payment_id UUID NOT NULL, loan_id UUID NOT NULL, amount NUMERIC(18, 2) NOT NULL, state VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE loan');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_order');
    }
}
