<?php

namespace Plugin\GiftWrapping;

use Eccube\Plugin\AbstractPluginManager;
use Plugin\GiftWrapping\Entity\Wrapping;
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

    /**
     * @var string コピー元ブロックファイル
     */
    private $originBlock;

    public function __construct()
    {
        // コピー元のディレクトリ
        $this->origin = __DIR__ . '/Resource/assets';
        // コピー先のディレクトリ
        $this->target = __DIR__ . '/../../../html/plugin/giftwrapping';
    }

    /**
     * プラグインインストール時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function install($config, $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);

        // リソースファイルのコピー
        $this->copyAssets();

    }

    /**
     * プラグイン削除時の処理
     *
     * @param $config
     * @param $app
     */
    public function uninstall($config, $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code'], 0);

        // リソースファイルの削除
        $this->removeAssets();
    }

    /**
     * プラグイン有効時の処理
     *
     * @param $config
     * @param $app
     * @throws \Exception
     */
    public function enable($config, $app)
    {

        $em = $app['orm.em'];
        $em->getConnection()->beginTransaction();
        try {

            // serviceで定義している情報が取得できないため、直接呼び出す
            try {
                // EC-CUBE3.0.3対応
                $Wrapping = $em->getRepository('Plugin\GiftWrapping\Entity\Wrapping')->find(1);
            } catch (\Exception $e) {
                return null;
            }

            if (!$Wrapping) {

                $Wrapping = new Wrapping();

                // IDは1固定
                $Wrapping->setId(1);
                $em->persist($Wrapping);
                $em->flush($Wrapping);

            }

            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * プラグイン無効時の処理
     *
     * @param $config
     * @param $app
     */
    public function disable($config, $app)
    {
    }

    public function update($config, $app)
    {
    }


    /**
     * 画像ファイル等をコピー
     */
    private function copyAssets()
    {
        $file = new Filesystem();
        $file->mirror($this->origin, $this->target . '/assets');
    }

    /**
     * コピーした画像ファイルなどを削除
     */
    private function removeAssets()
    {
        $file = new Filesystem();
        $file->remove($this->target);
    }

}
