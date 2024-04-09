<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409094935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD4584665A');
        $this->addSql('DROP INDEX IDX_D34A04AD4584665A ON product');
        $this->addSql('ALTER TABLE product CHANGE product_id subcategory_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD5DC6FE57 FOREIGN KEY (subcategory_id) REFERENCES sub_category (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD5DC6FE57 ON product (subcategory_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD5DC6FE57');
        $this->addSql('DROP INDEX IDX_D34A04AD5DC6FE57 ON product');
        $this->addSql('ALTER TABLE product CHANGE subcategory_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4584665A FOREIGN KEY (product_id) REFERENCES sub_category (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D34A04AD4584665A ON product (product_id)');
    }
}
