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
use Plugin\Point\Entity\PointInfo;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;


class PointServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // ポイント機能基本情報テーブル用リポジトリ
        $app['eccube.plugin.point.repository.pointinfo'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\Point\Entity\PointInfo');
        });

        /*
        var_dump($app['eccube.plugin.point.repository.pointinfo']);
        var_dump('OK');
        exit();
        */

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new PointType($app);
            return $types;
        }));

        // 一覧
        $app->match('/point/setting', 'Plugin\Point\Controller\PointController::index')->bind('point');

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
