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

use Doctrine\DBAL\Exception\DatabaseObjectNotFoundException;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\PageLayout;
use Eccube\Plugin\AbstractPluginManager;

/**
 * Class PluginManager
 * インストールハンドラー
 */
class PluginManager extends AbstractPluginManager
{

    /**
     * Image folder path (cop source)
     * @var type
     */
    protected $imgSrc;
    /**
     *Image folder path (copy destination)
     * @var type
     */
    protected $imgDst;

    protected $app;

    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * @param $config
     * @param $app
     */
    public function install($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/resource/doctrine/mygration', $config['code']);
        //$this->insertDefaultToPointInfo();
    }

    /**
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/resource/doctrine/mygration', $config['code'], 0);
    }

    public function enable($config, $app)
    {

        $deviceType = $this->app['eccube.repository.master.device_type']->findOneById(10);
        $pageLayout = new PageLayout();
        $pageLayout->setId(null);
        $pageLayout->setDeviceType($deviceType);
        $pageLayout->setFileName('../../Plugin/Point/Resource/template/default/point_use');
        $pageLayout->setEditFlg(2);
        $pageLayout->setMetaRobots('noindex');
        $pageLayout->setUrl('point_use');
        $pageLayout->setName('商品購入確認/利用ポイント');

        $this->app['orm.em']->persist($pageLayout);
        $this->app['orm.em']->flush($pageLayout);
    }

    public function disable($config, $app)
    {
        // ログテーブルからポイントを計算
        //$qb = $this->createQueryBuilder();
        $pageLayout = $this->app['eccube.repository.page_layout']->findByUrl('point_use');

        /*
        dump($qb->getDql());
        exit();
        */

        //dump($qb->getDQL());
        //exit();
        foreach($pageLayout as $deleteNode) {
            $this->app['orm.em']->persist($deleteNode);
            $this->app['orm.em']->remove($deleteNode);
        }
        $this->app['orm.em']->flush();

    }

    public function update($config, $app)
    {

    }

    protected function insertDefaultToPointInfo(){
        // ポイント基本情報初期値設定
        try {
            $pointInfo = new Entity\PointInfo();
            $pointInfo->setPlgAddPointStatus(1);
            $pointInfo->setPlgBasicPointRate(1);
            $pointInfo->setPlgPointConversionRate(1);
            $pointInfo->setPlgRoundType(0);
            $pointInfo->setPlgCalculationType(0);
            $this->app['orm.em']->persist($pointInfo);
            $this->app['orm.em']->flush($pointInfo);
        }catch(DatabaseObjectNotFoundException $e){
            throw new DatabaseObjectNotFoundException();
        }
    }

}
