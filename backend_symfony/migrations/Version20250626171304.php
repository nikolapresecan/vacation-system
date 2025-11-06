<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250626171304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE request CHANGE team_id team_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request ADD CONSTRAINT FK_3B978F9F296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3B978F9F296CD8AE ON request (team_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3B978F9F296CD8AE ON request
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request CHANGE team_id team_id INT NOT NULL
        SQL);
    }
}
