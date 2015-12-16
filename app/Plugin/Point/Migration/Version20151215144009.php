<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151215144009 extends AbstractMigration
{
    const PLG_POINT_INFO = 'plg_point_info';
    const PLG_POINT_CUSTOMER = 'plg_point_customer';
    const PLG_POINT_PRODUCT = 'plg_point_product';
    const PLG_POINT_ORDER = 'plg_point_order';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // ポイント機能基本情報格納テーブルを追加
        if ($schema->hasTable(self::PLG_POINT_INFO)) {
            return true;
        }
        $t = $schema->createTable(self::PLG_POINT_INFO);
        $t->addColumn('id', 'smallint', array('NotNull' => true));
        $t->addColumn('basic_point_rate', 'decimal', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('add_point_status', 'smallint', array('NotNull' => false, 'Default' => 1));
        $t->addColumn('point_conversion_rate', 'decimal', array('NotNull' => true, 'Default' => null));
        $t->setPrimaryKey(array('id'));

        // ポイント機能会員テーブル
        if ($schema->hasTable(self::PLG_POINT_CUSTOMER)) {
            return true;
        }
        $t = $schema->createTable(self::PLG_POINT_CUSTOMER);
        $t->addColumn('id', 'smallint', array('NotNull' => true));
        $t->addColumn('point', 'decimal', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('created', 'date', array('NotNull' => true));
        $t->addColumn('modified', 'date', array('NotNull' => true));
        $t->setPrimaryKey(array('id'));

        // ポイント機能商品テーブル
        if ($schema->hasTable(self::PLG_POINT_PRODUCT)) {
            return true;
        }
        $t = $schema->createTable(self::PLG_POINT_PRODUCT);
        $t->addColumn('id', 'smallint', array('NotNull' => true));
        $t->addColumn('product_id', 'smallint', array('NotNull' => true));
        $t->addColumn('product_point_rate', 'decimal', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('created', 'date', array('NotNull' => true));
        $t->addColumn('modified', 'date', array('NotNull' => true));
        $t->setPrimaryKey(array('id'));

        // ポイント機能受注テーブル
        if ($schema->hasTable(self::PLG_POINT_ORDER)) {
            return true;
        }
        $t = $schema->createTable(self::PLG_POINT_ORDER);
        $t->addColumn('id', 'smallint', array('NotNull' => true));
        $t->addColumn('customer_id', 'smallint', array('NotNull' => true));
        $t->addColumn('order_id', 'smallint', array('NotNull' => true));
        $t->addColumn('use_point', 'decimal', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('add_point', 'decimal', array('NotNull' => true, 'Default' => 0));
        $t->addColumn('use_point_date', 'datetime', array('NotNull' => true));
        $t->addColumn('add_point_confirm_date', 'datetime', array('NotNull' => true));
        $t->addColumn('add_point_confirm_status', 'smallint', array('NotNull' => false, 'Default' => 0));
        $t->addColumn('created', 'datetime', array('NotNull' => true));
        $t->addColumn('modified', 'datetime', array('NotNull' => true));
        $t->setPrimaryKey(array('id'));
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
