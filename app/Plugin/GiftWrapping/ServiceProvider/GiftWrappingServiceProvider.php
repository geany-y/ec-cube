<?php

namespace Plugin\GiftWrapping\ServiceProvider;

use Eccube\Application;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\GiftWrapping\Form\Extension\ShoppingTypeExtension;
use Plugin\GiftWrapping\Form\Type\GiftWrappingConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class GiftWrappingServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
        // 管理画面
        $app->match('/' . $app['config']['admin_route'] . '/plugin/giftwrapping/config', 'Plugin\GiftWrapping\Controller\ConfigController::index')->bind('plugin_GiftWrapping_config');

        $app->match('/plugin/giftwrapping/checkout', 'Plugin\GiftWrapping\Controller\GiftWrappingController::index')->bind('plugin_giftwrapping_index');

        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new GiftWrappingConfigType($app);
            return $types;
        }));

        // Form Extension
        $app['form.type.extensions'] = $app->share($app->extend('form.type.extensions', function ($extensions) use ($app) {
            $extensions[] = new ShoppingTypeExtension($app);
            return $extensions;
        }));

        // Repository
        $app['eccube.plugin.repository.wrapping'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\GiftWrapping\Entity\Wrapping');
        });

        // Service
        $app['eccube.plugin.service.gift_wrapping'] = $app->share(function () use ($app) {
            return new \Plugin\GiftWrapping\Service\GiftWrappingService($app);
        });

        // メッセージ登録
        $app['translator'] = $app->share($app->extend('translator', function ($translator, \Silex\Application $app) {
            $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());
            $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $app['locale']);
            }
            return $translator;
        }));

        // load config
        $conf = $app['config'];
        $app['config'] = $app->share(function () use ($conf) {
            $confarray = array();
            $path_file = __DIR__ . '/../Resource/config/path.yml';
            if (file_exists($path_file)) {
                $config_yml = Yaml::parse(file_get_contents($path_file));
                if (isset($config_yml)) {
                    $confarray = array_replace_recursive($confarray, $config_yml);
                }
            }

            $constant_file = __DIR__ . '/../Resource/config/constant.yml';
            if (file_exists($constant_file)) {
                $config_yml = Yaml::parse(file_get_contents($constant_file));
                if (isset($config_yml)) {
                    $confarray = array_replace_recursive($confarray, $config_yml);
                }
            }

            return array_replace_recursive($conf, $confarray);
        });

        // ログファイル設定
        $app['monolog.gift.wrapping'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('gift.wrapping.client');

            $file = $app['config']['root_dir'] . '/app/log/giftwrapping.log';
            $RotateHandler = new RotatingFileHandler($file, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'giftwrapping_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::INFO)
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {
    }
}
