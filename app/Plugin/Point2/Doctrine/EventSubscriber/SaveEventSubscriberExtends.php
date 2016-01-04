<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Plugin\Point\Doctrine\EventSubscriber;

use Eccube\Doctrine\EventSubscriber\SaveEventSubscriber;
/*
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eccube\Application;
*/
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

//class SaveEventSubscriberExtends extends SaveEventSubscriber implements EventSubscriberInterface
class SaveEventSubscriberExtends implements EventSubscriberInterface
{

    public function __construct(\Eccube\Application $app){
        //parent::__construct($app);
    }

    public static function getSubscribedEvents()
    {
        //return array('method' => 'prePersist');
        return array(
            Events::prePersist,
            Events::preUpdate,
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        //var_dump($args);
        //exit();
        /*
        $entity = $args->getObject();

        if (method_exists($entity, 'setCreateDate')) {
            $entity->setCreateDate(new \DateTime());
        }
        if (method_exists($entity, 'setUpdateDate')) {
            $entity->setUpdateDate(new \DateTime());
        }

        if ($this->app['security']->getToken() && $this->app['security']->isGranted('ROLE_ADMIN') && method_exists($entity, 'setCreator')) {
            $Member = $this->app['security']->getToken()->getUser();
            $entity->setCreator($Member);
        }
        */
    }
}
