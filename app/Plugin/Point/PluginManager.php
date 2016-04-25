<?php

namespace Plugin\Point;

use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;
use Monolog\Logger;

/**
 * インストールハンドラー
 * Class PluginManager
 * @package Plugin\Point
 */
class PluginManager extends AbstractPluginManager
{
    /** @var \Eccube\Application */
    protected $app;

    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * インストール時に実行
     * @param $config
     * @param $app
     */
    public function install($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code']);
    }

    /**
     * アンインストール時に実行
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * プラグイン有効化時に実行
     * @param $config
     * @param $app
     */
    public function enable($config, $app)
    {
        $qb = $this->app['db']->createQueryBuilder();
        $qb
            ->insert('plg_point_info')
            //->setValue('plg_point_info_id', ':plgPointInfoId')
            ->setValue('plg_add_point_status', ':plgAddPointStatus')
            ->setValue('plg_basic_point_rate', ':plgBasicPointRate')
            ->setValue('plg_point_conversion_rate', ':plgPointConversionRate')
            ->setValue('plg_round_type', ':plgRoundType')
            ->setValue('plg_calculation_type', ':plgCalculationType')
            ->setValue('create_date', ':CreateDate')
            ->setValue('update_date', ':UpdateDate')
            //->setParameter('plgPointInfoId', null)
            ->setParameter('plgAddPointStatus', 1)
            ->setParameter('plgBasicPointRate', 1)
            ->setParameter('plgPointConversionRate', 1)
            ->setParameter('plgRoundType', 1)
            ->setParameter('plgCalculationType', 1)
            ->setParameter('CreateDate', date('Y-m-d h:i:s'))
            ->setParameter('UpdateDate', date('Y-m-d h:i:s'));

        $qb->execute();

        // ページレイアウトにプラグイン使用時の値を代入
        $deviceType = $this->app['eccube.repository.master.device_type']->findOneById(10);
        $pageLayout = new PageLayout();
        $pageLayout->setId(null);
        $pageLayout->setDeviceType($deviceType);
        $pageLayout->setFileName('../../Plugin/Point/Resource/template/default/point_use');
        $pageLayout->setEditFlg(2);
        $pageLayout->setMetaRobots('noindex');
        $pageLayout->setUrl('point_use');
        $pageLayout->setName('商品購入確認/利用ポイント');
        try {
            $this->app['orm.em']->persist($pageLayout);
            $this->app['orm.em']->flush($pageLayout);
        } catch (\Exception $e) {
            $log = $e;
            $this->app->log($log, array(), Logger::WARNING);
            $app->addError('プラグインの有効化に失敗いたしました', 'admin');
        }
    }

    /**
     * プラグイン無効化時実行
     * @param $config
     * @param $app
     */
    public function disable($config, $app)
    {
        $qb = $this->app['db']->createQueryBuilder();
        $qb->delete('plg_point_info');
        $qb->execute();
        // ログテーブルからポイントを計算
        $pageLayout = $this->app['eccube.repository.page_layout']->findByUrl('point_use');

        // 重複登録対応
        foreach ($pageLayout as $deleteNode) {
            $this->app['orm.em']->persist($deleteNode);
            $this->app['orm.em']->remove($deleteNode);
        }
        try {
            $this->app['orm.em']->flush();
        } catch (\Exception $e) {
            $log = $e;
            $this->app->log($log, array(), Logger::WARNING);
            $app->addError('プラグインの無効化に失敗いたしました', 'admin');
        }
    }

    /**
     * アップデート時に行う処理
     * @param $config
     * @param $app
     */
    public function update($config, $app)
    {
    }
}
