<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140630113658 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE relish (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, space_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D7778E1FF675F31B (author_id), INDEX IDX_D7778E1F23575340 (space_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE relish ADD CONSTRAINT FK_D7778E1FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)");
        $this->addSql("ALTER TABLE relish ADD CONSTRAINT FK_D7778E1F23575340 FOREIGN KEY (space_id) REFERENCES space (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE relish");
    }
}
