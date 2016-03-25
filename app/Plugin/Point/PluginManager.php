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
        $this->migrationSchema($app, __DIR__.'/Migration', $config['code']);
        $this->insertDefaultToPointInfo();
    }

    /**
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__.'/Migration', $config['code'], 0);
    }

    public function enable($config, $app)
    {

    }

    public function disable($config, $app)
    {

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
