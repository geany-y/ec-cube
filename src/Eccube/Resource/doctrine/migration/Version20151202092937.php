<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151202092937 extends AbstractMigration
{
    const BASE_INFO = 'dtb_base_info';
    const CUSTOMER = 'dtb_customer';
    const ORDER_DETAIL = 'dtb_order_detail';
    const PRODUCT_CLASS = 'dtb_product_class';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        //dtb_base_info
        $t=$schema->getTable(self::BASE_INFO);
        if (!$t->hasColumn('basic_point_rate')) {
            $t->addColumn('basic_point_rate', 'decimal', array('NotNull' => true, 'Default' => 0));
        }
        if (!$t->hasColumn('add_point_status')) {
            $t->addColumn('add_point_status', 'smallint', array('NotNull' => false, 'Default' => 1));
        }
        if (!$t->hasColumn('point_caliculate_type')) {
            $t->addColumn('point_caliculate_type', 'smallint', array('NotNull' => true, 'Default' => 0));
        }
        if (!$t->hasColumn('point_flg')) {
            $t->addColumn('point_flg', 'smallint', array('NotNull' => true, 'Default' => 0));
        }
        if (!$t->hasColumn('point_rounding_type')) {
            $t->addColumn('point_rounding_type', 'smallint', array('NotNull' => true, 'Default' => 0));
        }
        if (!$t->hasColumn('point_conversion_rate')) {
            $t->addColumn('point_conversion_rate', 'decimal', array('NotNull' => true, 'Default' => null));
        }

        //dtb_customer
        $t=$schema->getTable(self::CUSTOMER);
        if (!$t->hasColumn('point')) {
            $t->addColumn('point', 'decimal', array('NotNull' => true, 'Default' => 0));
        }

        //dtb_product_class
        $t=$schema->getTable(self::PRODUCT_CLASS);
        if (!$t->hasColumn('product_point_rate')) {
            $t->addColumn('product_point_rate', 'decimal', array('NotNull' => true, 'Default' => 0));
        }

        //dtb_order_detail
        $t=$schema->getTable(self::ORDER_DETAIL);
        if (!$t->hasColumn('use_point') && !$t->hasColumn('add_point')) {
            $t->addColumn('use_point', 'decimal', array('NotNull' => true, 'Default' => 0));
            $t->addColumn('add_point', 'decimal', array('NotNull' => true, 'Default' => 0));
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
