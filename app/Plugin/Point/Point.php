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
    /** @var  \Eccube\Application $app */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 商品登録・更新画面にポイント付与率項目を追加
     *
     * @param FilterResponseEvent $event
     */
    public function setProductPointRateColumn(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $html = $this->getHtmlProductPointRate($request, $response);
        $response->setContent($html);

        $event->setResponse($response);
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @return string
     */
    private function getHtmlProductPointRate(Request $request, Response $response)
    {

        $crawler = new Crawler($response->getContent());

        $html = $this->getHtml($crawler);

        $form = $this->app['form.factory']->createBuilder('admin_product_point_rate')->getForm();

        $form->handleRequest($request);

        $parts = $this->app->renderView('Point/Resource/template/product_point_rate_parts.twig', array(
            'form' => $form->createView()
        ));

        var_dump($parts);
        exit();

        try {
            // DOMに結合
            $oldHtml = $crawler->filter('.box_body')->last()->html();
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
