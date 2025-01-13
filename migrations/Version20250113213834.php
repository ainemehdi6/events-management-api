<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113213834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX BACK_USER_UNIQ_IDENTIFIER_UUID ON user');
        $this->addSql('ALTER TABLE user CHANGE auth_uuid uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX USER_UNIQ_IDENTIFIER_UUID ON user (uuid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX USER_UNIQ_IDENTIFIER_UUID ON `user`');
        $this->addSql('ALTER TABLE `user` CHANGE uuid auth_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX BACK_USER_UNIQ_IDENTIFIER_UUID ON `user` (auth_uuid)');
    }
}
