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

namespace Plugin\LikeNiko\ServiceProvider;

use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class LikeNikoServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // 一覧
        $app->match('/likeniko/publish', '\\Plugin\\LikeNiko\\Controller\\PublishController::index')->bind('publish');

        // 型登録
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new \Plugin\LikeNiko\Form\Type\LikeNikoType($app);
            return $types;
        }));
    }


    public function boot(BaseApplication $app)
    {
    }
}
