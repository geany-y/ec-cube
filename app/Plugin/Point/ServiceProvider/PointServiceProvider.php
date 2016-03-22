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

namespace Plugin\Point\ServiceProvider;

use Eccube\Application;
use Plugin\Point\Doctrine\Listener\ORMListener;
use Plugin\Point\Resource\lib\EventRoutineWorksHelper\EventRoutineWorksHelperFactory;
use Plugin\Point\Resource\lib\PointCalculateHelper\PointCalculateHelper;
use Plugin\Point\Resource\lib\PointCalculateHelper\PointCalculateHelperFactory;
use Plugin\Point\Resource\lib\PointHistoryHelper\PointHistoryHelper;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

/**
 * Class PointServiceProvider
 * @package Plugin\Point\ServiceProvider
 * @pram Eccube\Application
 */
class PointServiceProvider implements ServiceProviderInterface
{
    /**
     * レポジトリの登録
     * @param BaseApplication $app
     */
    protected function setRepository(BaseApplication $app)
    {
        // ポイント情報テーブル用リポジトリ
        $app['eccube.plugin.point.repository.point'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\Point\Entity\Point');
            }
        );

        // ポイント機能基本情報テーブル用リポジトリ
        $app['eccube.plugin.point.repository.pointinfo'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\Point\Entity\PointInfo');
            }
        );

        // ポイント付与タイミング受注ステータス保存テーブル用リポジトリ
        $app['eccube.plugin.point.repository.pointinfo.addstatus'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\Point\Entity\PointInfoAddStatus');
            }
        );

        // ポイント会員情報テーブル
        $app['eccube.plugin.point.repository.pointcustomer'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\Point\Entity\PointCustomer');
            }
        );

        // ポイント機能商品付与率テーブル
        $app['eccube.plugin.point.repository.pointproductrate'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\Point\Entity\PointProductRate');
            }
        );

        // ポイント機能スナップショットテーブル
        $app['eccube.plugin.point.repository.pointsnapshot'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\Point\Entity\PointSnapshot');
            }
        );
    }

    /**
     * Formタイプの登録
     * @param BaseApplication $app
     */
    protected function setFormType(BaseApplication $app)
    {
        // フォームタイプ登録
        $app['form.types'] = $app->share(
            $app->extend(
                'form.types',
                function ($types) use ($app) {
                    $types[] = new \Plugin\Point\Form\Type\PointInfoType($app);
                    $types[] = new \Plugin\Point\Form\Type\PointInfoAddStatusType($app);

                    return $types;
                }
            )
        );
    }

    /**
     * ルーティングの登録
     * @param BaseApplication $app
     */
    protected function setPointPluginRouting(BaseApplication $app)
    {
        // 管理画面 > 設定 > 基本情報設定 > ポイント基本情報設定画面
        $app->match(
            '/'.$app['config']['admin_route'].'/point/setting',
            'Plugin\Point\Controller\PointController::index'
        )->bind('point_info');

        // フロント画面 > 商品購入確認画面
        $app->match(
            '/shopping/use_point',
            'Plugin\Point\Controller\PointController::usePoint'
        )->bind('point_use');
    }

    /**
     * メニューの登録
     * @param BaseApplication $app
     */
    protected function setAdminMenu(BaseApplication $app)
    {
        // メニュー登録
        $app['config'] = $app->share(
            $app->extend(
                'config',
                function ($config) {
                    $addNavi['id'] = "point_info";
                    $addNavi['name'] = "ポイント管理";
                    $addNavi['url'] = "point_info";
                    $nav = $config['nav'];
                    foreach ($nav as $key => $val) {
                        if ("setting" == $val["id"]) {
                            $nav[$key]['child'][0]['child'][] = $addNavi;
                        }
                    }
                    $config['nav'] = $nav;

                    return $config;
                }
            )
        );
    }

    /**
     * サービス登録処理
     * @param BaseApplication $app
     */
    public function register(BaseApplication $app)
    {
        // ルーティング登録
        $this->setPointPluginRouting($app);

        // レポジトリ登録
        $this->setRepository($app);

        // フォームタイプ登録
        $this->setFormType($app);

        // メニュー登録
        $this->setAdminMenu($app);

        // フックポイントイベント定型処理ヘルパーファクトリー登録
        $app['eccube.plugin.point.hookpoint.routinework.helper.factory'] = $app->share(
            function () {
                return new EventRoutineWorksHelperFactory();
            }
        );

        // ポイント計算処理サービスファクトリー登録
        $app['eccube.plugin.point.calculate.helper.factory'] = $app->share(
            function () use($app) {
                return new PointCalculateHelper($app);
            }
        );

        // ポイント履歴ヘルパー登録
        $app['eccube.plugin.point.history.service'] = $app->share(
            function () {
                return new PointHistoryHelper();
            }
        );

        // メッセージ登録
        $app['translator'] = $app->share(
            $app->extend(
                'translator',
                function ($translator, \Silex\Application $app) {
                    $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
                    $file = __DIR__.'/../Resource/locale/message.'.$app['locale'].'.yml';
                    if (file_exists($file)) {
                        $translator->addResource('yaml', $file, $app['locale']);
                    }

                    return $translator;
                }
            )
        );
    }

    public function boot(BaseApplication $app)
    {
    }
}
