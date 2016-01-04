<?php
namespace Plugin\Point2\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
//use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
//use Doctrine\ORM\Event\LifecycleEventArgs;
// for Doctrine 2.4: Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use AppBundle\Entity\Product;

class ProductUpsertSubscriber implements EventSubscriber
{
    protected $app;

    public function __construct($app){
        //$app->setSameApp();
        //$this->app = $app['eccube.app'];
        $this->app = $app;
    }

    public function onFlush($args)
    {
        //$uow = $this->app['orm.em']->getUnitOfWork();
        //var_dump(get_class_methods($uow));
        //var_dump($connection->lastInsertId());

        // Doctrine\ORM\Event\OnFlushEventArgs


        //var_dump('In Plugin');
        //var_dump(spl_object_hash($this->app['orm.em']));
        //$Product = new \Eccube\Entity\Product();
        //var_dump($lastId);

        $uow = $args->getEntityManager()->getUnitOfWork();
        //getScheduledEntityInsertions
        //foreach ($uow->getScheduledEntity() as $updated) {
        //var_dump($uow->getScheduledEntityInsertions());
        // follow the on Insert
        foreach ($uow->getScheduledEntityInsertions() as $updated) {
            var_dump('<br />Point2拡張<br />');
            var_dump(get_class($updated).'<br />');
            if ($updated instanceof \Eccube\Entity\Product) {
                // save PointProductRojicHere //Product Entity HERE()
                //$em->persist(new IssueLog($updated));
            }
        }

        $point_rate = $this->app['request']->request->get('admin_product_point_rate');
        var_dump('Point2拡張<br />');
        var_dump($point_rate);
        exit();
        //$ProductPointRate = new \Plugin\Point\Entity\ProductPointRate();
        //$ProductPointRate->setId(1);
        /*
        $ProductPointRate->setProductPointRate($point_rate['product_point_rate']);
        $ProductPointRate->setProductClassId($ProductClass->getId());

        $date = new \DateTime("now");
        $ProductPointRate->setCreated($date);
        $ProductPointRate->setModified($date);
        $app['orm.em']->persist($ProductPointRate);
        $res = $app['eccube.plugin.point.repository.pointproduct']->save($ProductPointRate);
        */
        //exit();

        //$uow->computeChangeSets();
    }

    public function getSubscribedEvents()
    {
        //return array(Events::onFlush, array($this, 'onFlush'));
        return array('onFlush');
        //return array(Events::onFlush);
    }
}
