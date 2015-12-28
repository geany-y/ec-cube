<?php
namespace Plugin\Point\Doctrine\Listener;

use Eccube\Application;
use Doctrine\ORM\Mapping\PostPersist;


/*
 * ORMListener class
 *
 * @author:        Marco Aurelio Simao
 * @description:   Listener para realizar operacoes em qualquer objeto manipulado pelo Doctrine 2.2
 */

use Doctrine\ORM\UnitOfWork;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Enova\EntitiesBundle\Entity\Entidades;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Enova\EntitiesBundle\Entity\Tagged;
use Enova\EntitiesBundle\Entity\Tags;

class ORMListener
{
    protected $extra_update;

    public function __construct(Application $app)
    {
        $this->container    = $app;
        $this->extra_update = false;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
      var_dump('Now On Flush');
      exit();
        $securityContext = $this->container->get('security.context');
        $em              = $args->getEntityManager();

        $uow             = $em->getUnitOfWork();
        $cmf             = $em->getMetadataFactory();

        foreach ($uow->getScheduledEntityInsertions() AS $entity)
        {
            $meta = $cmf->getMetadataFor(get_class($entity));

            $this->updateTagged($em, $entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity)
        {
            $meta = $cmf->getMetadataFor(get_class($entity));

            $this->updateTagged($em, $entity);
        }
    }

    public function updateTagged($em, $entity)
    {
      echo '<pre>';
      var_dump($entity);
      echo '</pre>';
      exit();
      var_dump($entity);
      var_dump('Now On UpdateTagged');
      $entityTags = $entity->getTags();

      $a = array_shift($entityTags);
      //in my case, i have already sent the object from the form, but you could just replace this part for new Object() etc

      $uow      = $em->getUnitOfWork();
      $cmf      = $em->getMetadataFactory();
      $meta     = $cmf->getMetadataFor(get_class($a));

      $em->persist($a);

      $uow->computeChangeSet($meta, $a);
    }
}