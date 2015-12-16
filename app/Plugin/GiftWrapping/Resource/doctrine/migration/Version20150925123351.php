<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150925123351 extends AbstractMigration
{

    const NAME = 'plg_gift_wrapping';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($schema->hasTable(self::NAME)) {
            return true;
        }
        $table = $schema->createTable(self::NAME);
        $table->addColumn('gift_wrapping_id', 'integer', array(
            'unsigned' => true
        ));
        $table->addColumn('user_id', 'text', array('notnull' => false));
        $table->addColumn('user_password', 'text', array('notnull' => false));
        $table->addColumn('is_wrapping', 'boolean', array('notnull' => false));

        $table->setPrimaryKey(array('gift_wrapping_id'));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::NAME)) {
            return true;
        }
        $schema->dropTable(self::NAME);
    }
}
