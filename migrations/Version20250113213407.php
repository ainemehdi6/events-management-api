<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113213407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON user');
        $this->addSql('ALTER TABLE user ADD firstname VARCHAR(255) NOT NULL, ADD lastname VARCHAR(255) NOT NULL, ADD active TINYINT(1) NOT NULL, ADD auth_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX BACK_USER_UNIQ_IDENTIFIER_UUID ON user (auth_uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX BACK_USER_UNIQ_IDENTIFIER_UUID ON `user`');
        $this->addSql('ALTER TABLE `user` DROP firstname, DROP lastname, DROP active, DROP auth_uuid, CHANGE email email VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON `user` (email)');
    }
}
