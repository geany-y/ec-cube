<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\Point2\Form\Event;

use Eccube\ServiceProvider;
use Eccube\Entity\ProductClass;
use Symfony\Component\Form\FormEvent;
use Eccube\Application;
use Eccube\Common\Constant;

/*
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
*/
//use Symfony\Component\EventDispatcher\EventDispatcher;
//use Acme\StoreBundle\Event\StoreSubscriber;


class PointEvent
{

    public function __construct(){
    }

    public function onPostSetData(FormEvent $event){
    }
}
