<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140705102824 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE space_link ADD relation SMALLINT NOT NULL");
        $this->addSql("UPDATE space_link SET relation = is_author");
        $this->addSql("ALTER TABLE space_link DROP is_author");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE space_link ADD is_author TINYINT(1) DEFAULT '0' NOT NULL");
        $this->addSql("UPDATE space_link SET is_author = CASE WHEN relation = 1 THEN 1 ELSE 0 END");
        $this->addSql("ALTER TABLE space_link DROP relation");
    }
}
