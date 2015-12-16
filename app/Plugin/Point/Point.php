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

namespace Plugin\Point;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class Point
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

     public function onKernelController($event)
    {
        $controller = $event->getController();
    }

	public function setPointMenu(FilterResponseEvent $event)
    {
        $crawler = new Crawler($event->getResponse()->getContent());

        $html = $this->getHtml($crawler);

        $form = $this->app['form.factory']->createBuilder('admin_point')->getForm();

        $form->handleRequest($event->getRequest());

        //$parts = $this->app->renderView('Point/Resource/template/point_parts.twig', array(
        //    'form' => $form->createView()
        //));
        $parts = '<h1>test</h1>';

        //var_dump($oldHtml);
        //exit();

        try {
            $oldHtml = $crawler->filter('#confirm_main')->last()->html();
            $newHtml = $oldHtml . $parts;
            $html = str_replace($oldHtml, $newHtml, $html);

        } catch (\InvalidArgumentException $e) {
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }

     /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
     */
    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
}
