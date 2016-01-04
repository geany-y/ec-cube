<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151219103206 extends AbstractMigration
{
    const PLG_LIKENIKO_INFO = 'plg_likeniko_info';
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // ポイント機能基本情報格納テーブルを追加
        if ($schema->hasTable(self::PLG_LIKENIKO_INFO)) {
            return true;
        }
        $t = $schema->createTable(self::PLG_LIKENIKO_INFO);
        $t->addColumn('id', 'smallint', array('NotNull' => true));
        $t->addColumn('is_auth_flg', 'smallint', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('form_insert_key', 'string', array('NotNull' => true, 'Default' => ''));
        $t->addColumn('replace_block_key', 'string', array('NotNull' => true, 'Default' => ''));
        $t->addColumn('target_img_name', 'string', array('NotNull' => true, 'Default' => ''));
        $t->addColumn('target_img_height', 'smallint', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('target_img_width', 'smallint', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('node_server_address', 'string', array('NotNull' => true, 'Default' => ''));
        $t->setPrimaryKey(array('id'));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable(self::PLG_LIKENIKO_INFO)) {
            return true;
        }
        $t = $schema->dropTable(self::PLG_LIKENIKO_INFO);
    }
}
