<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160616155605 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if ($schema->hasTable('dtb_customer')) {
            $table = $schema->getTable('dtb_customer');
            $table->addColumn('department', 'string', array('NotNull' => false, 'length' => 255));
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if ($schema->hasTable('dtb_customer')) {
            $table = $schema->getTable('dtb_customer');
            $table->dropColumn('department');
        }
    }
}
