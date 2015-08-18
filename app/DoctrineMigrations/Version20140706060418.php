<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140706060418 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, space_id INT DEFAULT NULL, content_id VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, content_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_C53D045FF675F31B (author_id), INDEX IDX_C53D045F23575340 (space_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE image ADD CONSTRAINT FK_C53D045FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)");
        $this->addSql("ALTER TABLE image ADD CONSTRAINT FK_C53D045F23575340 FOREIGN KEY (space_id) REFERENCES space (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE image");
    }
}
