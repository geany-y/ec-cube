<?php
namespace Plugin\Point\Doctrine\EventSubscriber;

use Doctrine\Common\EventSubscriber;
//use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
//use Doctrine\ORM\Event\LifecycleEventArgs;
// for Doctrine 2.4: Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use AppBundle\Entity\Product;
use Plugin\Point\Entity\ProductPointRate;

class OnFlushSubscriber implements EventSubscriber
{
    protected $app;

    public function __construct($app){
        //$app->setSameApp();
        //$this->app = $app['eccube.app'];
        $this->app = $app;
    }

    public function onFlush($args)
    {
        ezd('onFlush');
        throw new \Exception('ロールバックチェック');
        //ezd(get_class_methods($args->getEntityManager()));

        /*
        $date = new \DateTime("now");
        $findProductFlg = false;
        $uow = $args->getEntityManager()->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $updated) {
            if ($updated instanceof \Eccube\Entity\Product) {
                $ProductPoint = new \Plugin\Point\Entity\ProductPointRate();
                $ProductPoint->setProduct($updated);
                $findProductFlg = true;
            }
        }

        $point_rate = '';
        $point_rate = $this->app['request']->request->get('admin_product_point_rate');

        if ($findProductFlg && !empty($point_rate)) {
            $ProductPoint->setProductPointRate($point_rate['product_point_rate']);
        }

        var_dump(get_class_methods($ProductPoint));

        $ProductPoint->setCreated($date);
        $ProductPoint->setModified($date);
        $uow->persist($ProductPoint);
        //$uow->flush();

        $uow->computeChangeSets();
        */
    }

    public function getSubscribedEvents()
    {
        //return array(Events::onFlush, array($this, 'onFlush'));
        return array('onFlush');
        //return array(Events::onFlush);
    }
}