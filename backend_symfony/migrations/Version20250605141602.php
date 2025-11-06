<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605141602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE approval_status (id INT AUTO_INCREMENT NOT NULL, request_id INT NOT NULL, team_leader_id INT DEFAULT NULL, project_manager_id INT DEFAULT NULL, team_leader_status_id INT DEFAULT NULL, project_manager_status_id INT DEFAULT NULL, team_leader_approval_date DATETIME DEFAULT NULL, project_manager_approval_date DATETIME DEFAULT NULL, team_leader_comment LONGTEXT DEFAULT NULL, project_manager_comment LONGTEXT DEFAULT NULL, INDEX IDX_5F84A795427EB8A5 (request_id), INDEX IDX_5F84A795C4105033 (team_leader_id), INDEX IDX_5F84A79560984F51 (project_manager_id), INDEX IDX_5F84A795B8390B38 (team_leader_status_id), INDEX IDX_5F84A795150F93BB (project_manager_status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, job_id INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, birth_date DATETIME NOT NULL, profile_picture VARCHAR(255) DEFAULT NULL, vacation_days INT NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, token_expiry DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_5D9F75A1E7927C74 (email), UNIQUE INDEX UNIQ_5D9F75A1F85E0677 (username), INDEX IDX_5D9F75A1BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE employee_role (employee_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_E2B0C02D8C03F15C (employee_id), INDEX IDX_E2B0C02DD60322AC (role_id), PRIMARY KEY(employee_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE holiday (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE job (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE request (id INT AUTO_INCREMENT NOT NULL, employee_id INT NOT NULL, status_id INT NOT NULL, number_of_days INT NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, comment VARCHAR(200) NOT NULL, created_date DATETIME NOT NULL, INDEX IDX_3B978F9F8C03F15C (employee_id), INDEX IDX_3B978F9F6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_7B00651C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_employee (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, team_leader_id INT DEFAULT NULL, project_manager_id INT DEFAULT NULL, INDEX IDX_E5DDA9DF296CD8AE (team_id), INDEX IDX_E5DDA9DFC4105033 (team_leader_id), INDEX IDX_E5DDA9DF60984F51 (project_manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE team_employee_members (team_employee_id INT NOT NULL, employee_id INT NOT NULL, INDEX IDX_727286D167331E11 (team_employee_id), INDEX IDX_727286D18C03F15C (employee_id), PRIMARY KEY(team_employee_id, employee_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status ADD CONSTRAINT FK_5F84A795427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status ADD CONSTRAINT FK_5F84A795C4105033 FOREIGN KEY (team_leader_id) REFERENCES employee (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status ADD CONSTRAINT FK_5F84A79560984F51 FOREIGN KEY (project_manager_id) REFERENCES employee (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status ADD CONSTRAINT FK_5F84A795B8390B38 FOREIGN KEY (team_leader_status_id) REFERENCES status (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status ADD CONSTRAINT FK_5F84A795150F93BB FOREIGN KEY (project_manager_status_id) REFERENCES status (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee_role ADD CONSTRAINT FK_E2B0C02D8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee_role ADD CONSTRAINT FK_E2B0C02DD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request ADD CONSTRAINT FK_3B978F9F8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request ADD CONSTRAINT FK_3B978F9F6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee ADD CONSTRAINT FK_E5DDA9DF296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee ADD CONSTRAINT FK_E5DDA9DFC4105033 FOREIGN KEY (team_leader_id) REFERENCES employee (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee ADD CONSTRAINT FK_E5DDA9DF60984F51 FOREIGN KEY (project_manager_id) REFERENCES employee (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee_members ADD CONSTRAINT FK_727286D167331E11 FOREIGN KEY (team_employee_id) REFERENCES team_employee (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee_members ADD CONSTRAINT FK_727286D18C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status DROP FOREIGN KEY FK_5F84A795427EB8A5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status DROP FOREIGN KEY FK_5F84A795C4105033
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status DROP FOREIGN KEY FK_5F84A79560984F51
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status DROP FOREIGN KEY FK_5F84A795B8390B38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE approval_status DROP FOREIGN KEY FK_5F84A795150F93BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee DROP FOREIGN KEY FK_5D9F75A1BE04EA9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee_role DROP FOREIGN KEY FK_E2B0C02D8C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employee_role DROP FOREIGN KEY FK_E2B0C02DD60322AC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F8C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE request DROP FOREIGN KEY FK_3B978F9F6BF700BD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee DROP FOREIGN KEY FK_E5DDA9DF296CD8AE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee DROP FOREIGN KEY FK_E5DDA9DFC4105033
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee DROP FOREIGN KEY FK_E5DDA9DF60984F51
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee_members DROP FOREIGN KEY FK_727286D167331E11
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE team_employee_members DROP FOREIGN KEY FK_727286D18C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE approval_status
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE employee
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE employee_role
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE holiday
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE job
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE role
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE status
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_employee
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE team_employee_members
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
