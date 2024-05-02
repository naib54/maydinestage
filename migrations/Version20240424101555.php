<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240501121611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       
        
        $this->addSql('CREATE TABLE size (id INT AUTO_INCREMENT NOT NULL, size VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE size_product (size_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_3627D6D5498DA827 (size_id), INDEX IDX_3627D6D54584665A (product_id), PRIMARY KEY(size_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
      
        $this->addSql('ALTER TABLE size_product ADD CONSTRAINT FK_3627D6D5498DA827 FOREIGN KEY (size_id) REFERENCES size (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE size_product ADD CONSTRAINT FK_3627D6D54584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
    
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
      
        $this->addSql('ALTER TABLE size_product DROP FOREIGN KEY FK_3627D6D5498DA827');
        $this->addSql('ALTER TABLE size_product DROP FOREIGN KEY FK_3627D6D54584665A');
        
        
      
        $this->addSql('DROP TABLE size');
        $this->addSql('DROP TABLE size_product');
   
    }
}

