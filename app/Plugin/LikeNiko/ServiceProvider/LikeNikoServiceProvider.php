<?php
namespace Plugin\LikeNiko\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class LikeNikoServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // LikeNiko基本設定
        $app->match('/'.$app['config']['admin_route'].'/likeniko/nikoinfo', '\Plugin\LikeNiko\Controller\NikoInfoController::index')->bind('nikoinfo');
        // メッセージ取得→Nodeへリレー
        $app->match('/likeniko/publish', '\Plugin\LikeNiko\Controller\PublishController::index')->bind('publish');

        // ポイント機能基本情報テーブル用リポジトリ
        $app['eccube.plugin.likeniko.repository.nikonfo'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\LikeNiko\Entity\NikoInfo');
        });

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\LikeNiko\Form\Type\LikeNikoType($app);
            $types[] = new \Plugin\LikeNiko\Form\Type\NikoInfoType($app);
            return $types;
        }));

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
            $addNavi['id'] = "nikoinfo";
            $addNavi['name'] = "ニコ動画風コメント設定";
            $addNavi['url'] = "nikoinfo";
            $nav = $config['nav'];
            foreach ($nav as $key => $val) {
                if ("content" == $val["id"]) {
                    $nav[$key]['child'][]  = $addNavi;
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
