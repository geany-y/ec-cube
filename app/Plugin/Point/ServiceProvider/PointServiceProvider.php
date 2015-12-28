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

//namespace Plugin\Point\ServiceProvider;

//use Silex\Application as BaseApplication;
//use Silex\ServiceProviderInterface;

namespace Plugin\Point\ServiceProvider;

use Eccube\Application;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\Point\Form\Type\PointType;
use Plugin\Point\Form\Type\ProductPointRateType;
use Plugin\Point\Doctrine\Listener\ORMListener;
use Plugin\Point\Entity\PointInfo;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\EventManager;


class PointServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // ポイント機能基本情報テーブル用リポジトリ
        $app['eccube.plugin.point.repository.pointinfo'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Point\Entity\PointInfo');
        });

        // ポイント機能関連商品情報テーブル用レポジトリ
        $app['eccube.plugin.point.repository.pointproduct'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('\Plugin\Point\Entity\ProductPointRate');
        });

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new PointType($app);
            $types[] = new ProductPointRateType($app);
            return $types;
        }));

        /*
        $app['doctrine.event_listener'] = $app->share(function (\Silex\Application $app) use($app){
                return new \Plugin\Point\Doctrine\Listener\ORMListener($app);
        });
        */
        // EventSubScriber Set
        $app['doctrine.event_subscriber'] = $app->share(function ($app) use($app) {
                return new \Plugin\Point\Doctrine\EventSubscriber\ProductUpsertSubscriber($app);
        });
        // Retunr Doctrine Event Manager
        $app['doctrine.em'] = $app->share(function ($app) use($app) {
                return new \Doctrine\Common\EventManager($app);
        });
        $app['eccube.service.cart'] = $container->extend('eccube.service.cart',function(Application $app) {
            return new \Eccube\Service\CartService($app);
        });
        /*
        $app['eccube.service.cart'] = $app->share(function () use ($app) {
            return new \Eccube\Service\CartService($app);
        });
        */
        // Form/Extension
        /*
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use($app) {
            $extensions[] = new \Plugin\Point\Form\Extension\ProductTypeExtension($app);
            return $extensions;
        }));
        */


        // 一覧
        $app->match('/admin/point/setting', 'Plugin\Point\Controller\PointController::index')->bind('point');

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }
            return $translator;
        }));

        // メニュー登録
        $app['config'] = $app->share($app->extend('config', function ($config) {
            $addNavi['id'] = "point";
            $addNavi['name'] = "ポイント管理";
            $addNavi['url'] = "point";
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ("setting" == $val["id"]) {
                    $nav[$key]['child'][0]['child'][]  = $addNavi;
                }
            }
            $config['nav'] = $nav;
            return $config;
        }));
    }



    public function boot(BaseApplication $app)
    {
    }
}
