<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Eccube\Entity\PageLayout;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160607155514 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$schema->hasTable('dtb_crud')) {
            $table = $schema->createTable('dtb_crud');
            $table->addColumn('id', 'integer', array(
                'autoincrement' => true,
            ));
            $table->addColumn('reason', 'smallint', array('NotNull' => true));
            $table->addColumn('name', 'string', array('NotNull' => true, 'length' => 255));
            $table->addColumn('title', 'string', array('NotNull' => true, 'length' => 255));
            $table->addColumn('notes', 'text', array('default' => 'null'));
            $table->addColumn('create_date', 'datetime', array('NotNull' => true));
            $table->addColumn('update_date', 'datetime', array('NotNull' => true));
            $table->setPrimaryKey(array('id'));
        }

        $app = \Eccube\Application::getInstance();
        $em = $app['orm.em'];
        $qb = $em->createQueryBuilder();

        $qb->select('pl')
            ->from('\Eccube\Entity\PageLayout', 'pl')
            ->where('pl.url = :Url')
            ->setParameter('Url', 'tutorial_crud');

        $res = $Point = $qb->getQuery()->getResult();

        if(count($res) < 1){
            $PageLayout = new PageLayout();
            $DeviceType = $em->getRepository('\Eccube\Entity\Master\DeviceType')->find(10);
            $PageLayout->setDeviceType($DeviceType);
            $PageLayout->setName('チュートリアル/CRUD');
            $PageLayout->setUrl('tutorial_crud');
            $PageLayout->setFileName('Tutorial/crud_top');
            $PageLayout->setEditFlg(2);

            $em->persist($PageLayout);
            $em->flush($PageLayout);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if (!$schema->hasTable('dtb_crud')) {
            $schema->dropTable('dtb_crud');
        }

        $app = \Eccube\Application::getInstance();
        $em = $app['orm.em'];
        $qb = $em->createQueryBuilder();

        $qb->select('pl')
            ->from('\Eccube\Entity\PageLayout', 'pl')
            ->where('pl.url = :Url')
            ->setParameter('Url', 'tutorial_crud');

        $res = $qb->getQuery()->getResult();

        if(count($res) > 0){
            $qb->delete()
                ->from('\Eccube\Entity\PageLayout', 'pl')
                ->where('pl.url = :Url')
                ->setParamater('Url', 'tutorial_crud');
            $res = $qb->getQuery()->execute();
        }
    }
}
