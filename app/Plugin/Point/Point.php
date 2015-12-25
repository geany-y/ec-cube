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

use Plugin\Point\Entity\ProductPointRate;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
//use Symfony\Component\EventDispatcher\EventDispatcher;
//use Acme\StoreBundle\Event\StoreSubscriber;


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

        //ユーザーからポストがあった際の保存処理
        $this->savePostDatas($request, $response);

        $html = $this->getHtmlProductPointRate($request, $response);
        $response->setContent($html);

        $event->setResponse($response);
    }

    public function savePostDatas(Request $request, Response $response){
      $id = $this->app['request']->attributes->get('id');
      echo '<script>alert('.$id.');</script>';
      /*
      $newc = $this->app['controllers_factory']->match('admin_product_product_edit', function(){ echo 'hoge'; exit();});

      echo '<pre>';
      $newc->getRoute()->run();
      echo '</pre>';
      exit();
      */
      //admin_product_product_edit
      //echo '</pre>';
      //exit();
      //$this->app->extend('controllers_factory', function($c){
      //});
        //var_dump($request->attributes->get('id'));
        //exit();
        //$form = $this->app['form.factory']->createBuilder('admin_product_point_rate')->getForm();
        //$curr_entity = $this->app['orm.em']->getUnitOfWork();
        /*
        echo '<pre>';
        //var_dump(get_class_methods($curr_entity));
        var_dump(array_keys($curr_entity->getIdentityMap()));
        echo '</pre>';
        exit();
        */
//エンティティマネージャーが保持している中身
/*
        array(12) {
  [0]=>
  string(32) "Eccube\Entity\PluginEventHandler"
  [1]=>
  string(20) "Eccube\Entity\Plugin"
  [2]=>
  string(25) "Eccube\Entity\Master\Work"
  [3]=>
  string(20) "Eccube\Entity\Member"
  [4]=>
  string(30) "Eccube\Entity\Master\Authority"
  [5]=>
  string(22) "Eccube\Entity\BaseInfo"
  [6]=>
  string(25) "Eccube\Entity\Master\Disp"
  [7]=>
  string(22) "Eccube\Entity\Category"
  [8]=>
  string(27) "Eccube\Entity\CategoryCount"
  [9]=>
  string(32) "Eccube\Entity\CategoryTotalCount"
  [10]=>
  string(32) "Eccube\Entity\Master\ProductType"
  [11]=>
  string(26) "Eccube\Entity\DeliveryDate"
}
*/

        /*
        // 保存処理
        if ('POST' === $request->getMethod()) {
            //$this->setPostDatas($form, $request);

            //フォーム生成(Ectentionsを取得)
            $form->setData($posts);
            $id = $this->app['']->createBuilder('admin_product_point_rate')->getForm();

            // バリデーション
            if ($form->isValid()) {
                var_dump('バリデーションOK');
                exit();
                $pointDatas = $form->getData();
                $status = $repo->save($SavePoint);
                if ($status) {
                    $app->addSuccess('admin.point.save.complete', 'admin');
                    return $app->redirect($app->url('point'));
                } else {
                    $app->addError('admin.point.save.error', 'admin');
                }
            }
        }

        return $app->render('Point/Resource/template/admin/pointinfo.twig', array(
            'form'          => $form->createView(),
            'Point'   => $Point,
        ));
        */
    }

    /**
     *
     * @param FormType $form
     * @param Request $request
     * @return FormType $form
     */
    private function setProductPointRateDatas($form, $request){
        $posts = $request->request->get('admin_product_point_rate');

        // PointRata作成
        $pointRate = $request->request->get('admin_product_point_rate');
        $ProductPointRate = new \Plugin\Point\Entity\ProductPointRate();
        foreach($pointRate as $key => $val){
            $method = "set".$this->camelize($key);
            call_user_func_array(array($ProductPointRate, $method), array($val));
        }
        $ProductPointRate->setCreated(date('Y-m-d H:i:s'));
        $ProductPointRate->setModified(date('Y-m-d H:i:s'));
    }

    private function camelize($str) {
        $str = strtr($str, '_', ' ');
        $str = ucwords($str);
        return str_replace(' ', '', $str);
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

        $parts = $this->app->renderView('Point/Resource/template/admin/product_point_rate_parts.twig', array(
            'form' => $form->createView()
        ));

        try {
            // DOMに結合
            $oldHtml = $crawler->filter('#admin_product_name')->parents()->parents()->parents()->html();

            //$oldHtml = $crawler->filter('#main_middle')->last()->html();

            $newHtml = $oldHtml . $parts;

            $html = str_replace($oldHtml, $newHtml, $html);
        } catch (\InvalidArgumentException $e) {
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
        //return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');

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
