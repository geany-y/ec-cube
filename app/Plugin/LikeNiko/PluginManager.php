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

namespace Plugin\LikeNiko;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Common\Constant;
use Eccube\Util\Cache;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
        /**
     * @var string コピー元リソースディレクトリ
     */
    private $origin;
    /**
     * @var string コピー先リソースディレクトリ
     */
    private $target;

    public function __construct()
    {
        // コピー元のディレクトリ
        $this->origin = __DIR__ . '/Resource/assets';
        // コピー先のディレクトリ
        $this->target = __DIR__ . '/../../../html/plugin/likeniko';
    }

    public function install($config, $app)
    {
        $this->migrationSchema($app, __DIR__.DIRECTORY_SEPARATOR.'Resource'.DIRECTORY_SEPARATOR.'doctrine'.DIRECTORY_SEPARATOR.'migration', $config['code']);
        // リソースファイルのコピー
        $this->copyAssets();
    }

    public function uninstall($config, $app)
    {
        // リソースファイルの削除
        $this->removeAssets();
        $this->migrationSchema($app, __DIR__.DIRECTORY_SEPARATOR.'Resource'.DIRECTORY_SEPARATOR.'doctrine'.DIRECTORY_SEPARATOR.'migration', $config['code'], 0);
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
    /**
     * JSファイルをコピー
     */
    private function copyAssets()
    {
        $file = new Filesystem();
        $file->mirror($this->origin, $this->target . '/assets');
    }
    /**
     * コピーしたJSファイルを削除
     */
    private function removeAssets()
    {
        $file = new Filesystem();
        $file->remove($this->target);
    }
}
