<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821091659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE employee ADD oib VARCHAR(11) DEFAULT NULL, ADD employment_date DATE DEFAULT NULL, ADD service_years INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_5D9F75A1AB498595 ON employee (oib)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request CHANGE team_id team_id INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_5D9F75A1AB498595 ON employee
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee DROP oib, DROP employment_date, DROP service_years
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request CHANGE team_id team_id INT DEFAULT NULL
        SQL);
    }
}
