<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240624011442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populating the country table';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            INSERT INTO
                `country` (name, alpha2, is_eu)
            VALUES
                ('Austria', 'AT', 1), 
                ('Belgium', 'BE', 1),
                ('Bulgary', 'BG', 1),
                ('Cyprus', 'CY', 1),
                ('Czech Republic', 'CZ', 1),
                ('Germany', 'DE', 1),
                ('Denmark', 'DK', 1),
                ('Estonia', 'EE', 1),
                ('Spain', 'ES', 1),
                ('Finland', 'FI', 1),
                ('France', 'FR', 1),
                ('Greece', 'GR', 1),
                ('Croatia', 'HR', 1),
                ('Hungury', 'HU', 1),
                ('Ireland', 'IE', 1),
                ('Italy', 'IT', 1),
                ('Lithuania', 'LT', 1),
                ('Luxembourg', 'LU', 1),
                ('Latvia', 'LV', 1),
                ('Malta', 'MT', 1),
                ('Netherlands', 'NL', 1),
                ('Poland', 'PO', 1),
                ('Portugal', 'PT', 1),
                ('Romania', 'RO', 1),
                ('Sweden', 'SE', 1), 
                ('Slovenia', 'SI', 1),
                ('Slovakia', 'SK', 1),
                ('Ukraine', 'UA', 0);
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE `country`;');
    }
}
