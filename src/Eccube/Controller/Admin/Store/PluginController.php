<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller\Admin\Store;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Exception\PluginException;
use Eccube\Util\Str;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class PluginController extends AbstractController
{

    /**
     * インストール済プラグイン画面
     *
     * @param Application $app
     * @param Request $request
     */
    public function index(Application $app, Request $request)
    {

        $pluginForms = array();
        $configPages = array();

        $Plugins = $app['eccube.repository.plugin']->findBy(array(), array('name' => 'ASC'));

        $officialPlugins = array();
        $unofficialPlugins = array();

        foreach ($Plugins as $Plugin) {

            $form = $app['form.factory']
                ->createNamedBuilder('form' . $Plugin->getId(), 'plugin_management', null, array(
                    'plugin_id' => $Plugin->getId(),
                ))
                ->getForm();

            $pluginForms[$Plugin->getId()] = $form->createView();

            try {
                // プラグイン用設定画面があれば表示(プラグイン用のサービスプロバイダーに定義されているか)
                $configPages[$Plugin->getCode()] = $app->url('plugin_' . $Plugin->getCode() . '_config');
            } catch (\Exception $e) {
                // プラグインで設定画面のルートが定義されていない場合は無視
            }

            if ($Plugin->getSource() == 0) {
                // 商品IDが設定されていない場合、非公式プラグイン
                $unofficialPlugins[] = $Plugin;
            } else {
                $officialPlugins[] = $Plugin;
            }

        }

        // オーナーズストアからダウンロード可能プラグイン情報を取得
        $BaseInfo = $app['eccube.repository.base_info']->get();

        $authKey = $BaseInfo->getAuthenticationKey();

        if (!is_null($authKey)) {

            // オーナーズストア通信
            $url = $app['config']['owners_store_url'] . '?method=list';
            list($json, $httpHeader) = $this->getRequestApi($request, $authKey, $url);

            if ($json) {

                // 接続成功時

                $data = json_decode($json, true);

                if (isset($data['success'])) {
                    $success = $data['success'];
                    if ($success == '1') {

                        // 既にインストールされているかどうか確認
                        foreach ($data['item'] as $item) {
                            foreach ($officialPlugins as $plugin) {
                                if ($plugin->getSource() == $item['product_id']) {
                                    // 商品IDが同一の情報を設定
                                    $plugin->setNewVersion($item['version']);
                                    $plugin->setLastUpdateDate($item['last_update_date']);
                                    $plugin->setProductUrl($item['product_url']);

                                    if ($plugin->getVersion() != $item['version']) {
                                        // バージョンが異なる
                                        if (in_array(Constant::VERSION, $item['eccube_version'])) {
                                            // 対象バージョン
                                            $plugin->setUpdateStatus(3);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        return $app->render('Store/plugin.twig', array(
            'plugin_forms' => $pluginForms,
            'officialPlugins' => $officialPlugins,
            'unofficialPlugins' => $unofficialPlugins,
            'configPages' => $configPages
        ));

    }

    /**
     * インストール済プラグインからのアップデート
     *
     * @param Application $app
     * @param Request $request
     * @param $id
     */
    public function update(Application $app, Request $request, $id)
    {

        $Plugin = $app['eccube.repository.plugin']->find($id);

        $form = $app['form.factory']
            ->createNamedBuilder('form' . $id, 'plugin_management', null, array(
                'plugin_id' => null, // placeHolder
            ))
            ->getForm();

        $message = '';

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $tmpDir = null;
                try {

                    $formFile = $form['plugin_archive']->getData();

                    $tmpDir = $app['eccube.service.plugin']->createTempDir();
                    $tmpFile = sha1(Str::random(32)) . '.' . $formFile->getClientOriginalExtension();

                    $formFile->move($tmpDir, $tmpFile);
                    $app['eccube.service.plugin']->update($Plugin, $tmpDir . '/' . $tmpFile);

                    $fs = new Filesystem();
                    $fs->remove($tmpDir);

                    $app->addSuccess('admin.plugin.update.complete', 'admin');

                    return $app->redirect($app->url('admin_store_plugin'));

                } catch (PluginException $e) {
                    if (!empty($tmpDir) && file_exists($tmpDir)) {
                        $fs = new Filesystem();
                        $fs->remove($tmpDir);
                    }
                    $message = $e->getMessage();
                }
            } else {
                $errors = $form->getErrors();
                foreach ($errors as $error) {
                    $message = $error->getMessage();
                    error_log($message);
                }

            }

        }

        $app->addError($message, 'admin');

        return $app->redirect($app->url('admin_store_plugin'));
    }


    /**
     * 対象のプラグインを有効にします。
     *
     * @param Application $app
     * @param $id
     */
    public function enable(Application $app, $id)
    {
        $Plugin = $app['eccube.repository.plugin']->find($id);

        if (!$Plugin) {
            throw new NotFoundHttpException();
        }

        if ($Plugin->getEnable() == Constant::ENABLED) {
            $app->addError('admin.plugin.already.enable', 'admin');
        } else {
            $app['eccube.service.plugin']->enable($Plugin);
            $app->addSuccess('admin.plugin.enable.complete', 'admin');
        }

        return $app->redirect($app->url('admin_store_plugin'));
    }

    /**
     * 対象のプラグインを無効にします。
     *
     * @param Application $app
     * @param $id
     */
    public function disable(Application $app, $id)
    {
        $Plugin = $app['eccube.repository.plugin']->find($id);

        if (!$Plugin) {
            throw new NotFoundHttpException();
        }

        if ($Plugin->getEnable() == Constant::ENABLED) {
            $app['eccube.service.plugin']->disable($Plugin);
            $app->addSuccess('admin.plugin.disable.complete', 'admin');
        } else {
            $app->addError('admin.plugin.already.disable', 'admin');
        }

        return $app->redirect($app->url('admin_store_plugin'));
    }


    /**
     * 対象のプラグインを削除します。
     *
     * @param Application $app
     * @param $id
     */
    public function uninstall(Application $app, $id)
    {
        $Plugin = $app['eccube.repository.plugin']->find($id);

        if (!$Plugin) {
            throw new NotFoundHttpException();
        }

        $app['eccube.service.plugin']->uninstall($Plugin);

        $app->addSuccess('admin.plugin.uninstall.complete', 'admin');

        return $app->redirect($app->url('admin_store_plugin'));
    }

    function handler(Application $app)
    {
        $handlers = $app['eccube.repository.plugin_event_handler']->getHandlers();

        // 一次元配列からイベント毎の二次元配列に変換する
        $HandlersPerEvent = array();
        foreach ($handlers as $handler) {
            $HandlersPerEvent[$handler->getEvent()][$handler->getHandlerType()][] = $handler;
        }

        return $app->render('Store/plugin_handler.twig', array(
            'handlersPerEvent' => $HandlersPerEvent
        ));

    }

    function handler_up(Application $app, $handlerId)
    {
        $repo = $app['eccube.repository.plugin_event_handler'];
        $repo->upPriority($repo->find($handlerId));

        return $app->redirect($app->url('admin_store_plugin_handler'));
    }

    function handler_down(Application $app, $handlerId)
    {
        $repo = $app['eccube.repository.plugin_event_handler'];
        $repo->upPriority($repo->find($handlerId), false);

        return $app->redirect($app->url('admin_store_plugin_handler'));
    }

    /**
     * プラグインファイルアップロード画面
     *
     * @param Application $app
     * @param Request $request
     */
    public function install(Application $app, Request $request)
    {
        $form = $app['form.factory']
            ->createBuilder('plugin_local_install')
            ->getForm();

        $errors = array();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $tmpDir = null;
                try {
                    $service = $app['eccube.service.plugin'];

                    $formFile = $form['plugin_archive']->getData();

                    $tmpDir = $service->createTempDir();
                    $tmpFile = sha1(Str::random(32)) . '.' . $formFile->getClientOriginalExtension(); // 拡張子を付けないとpharが動かないので付ける

                    $formFile->move($tmpDir, $tmpFile);

                    $service->install($tmpDir . '/' . $tmpFile);

                    $fs = new Filesystem();
                    $fs->remove($tmpDir);

                    $app->addSuccess('admin.plugin.install.complete', 'admin');

                    return $app->redirect($app->url('admin_store_plugin'));

                } catch (PluginException $e) {
                    if (!empty($tmpDir) && file_exists($tmpDir)) {
                        $fs = new Filesystem();
                        $fs->remove($tmpDir);
                    }
                    $errors[] = $e;
                }
            }
        }

        return $app->render('Store/plugin_install.twig', array(
            'form' => $form->createView(),
            'errors' => $errors,
        ));

    }

    /**
     * オーナーズストアプラグインインストール画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ownersInstall(Application $app, Request $request)
    {
        // オーナーズストアからダウンロード可能プラグイン情報を取得
        $BaseInfo = $app['eccube.repository.base_info']->get();

        $authKey = $BaseInfo->getAuthenticationKey();
        $authResult = true;
        $success = 0;
        $items = array();
        $promotionItems = array();
        $message = '';
        if (!is_null($authKey)) {

            // オーナーズストア通信
            $url = $app['config']['owners_store_url'] . '?method=list';
            list($json, $httpHeader) = $this->getRequestApi($request, $authKey, $url);

            if ($json === false) {
                // 接続失敗時
                $success = 0;

                $message = $this->getResponseErrorMessage($httpHeader);

            } else {
                // 接続成功時

                $data = json_decode($json, true);

                if (isset($data['success'])) {
                    $success = $data['success'];
                    if ($success == '1') {
                        $items = array();

                        // 既にインストールされているかどうか確認
                        $Plugins = $app['eccube.repository.plugin']->findAll();
                        $status = false;
                        // update_status 1 : 未インストール、2 : インストール済、 3 : 更新あり、4 : 有料購入
                        foreach ($data['item'] as $item) {
                            foreach ($Plugins as $plugin) {
                                if ($plugin->getSource() == $item['product_id']) {
                                    if ($plugin->getVersion() == $item['version']) {
                                        // バージョンが同じ
                                        $item['update_status'] = 2;
                                    } else {
                                        // バージョンが異なる
                                        $item['update_status'] = 3;
                                    }
                                    $items[] = $item;
                                    $status = true;
                                    break;
                                }
                            }
                            if (!$status) {
                                // 未インストール
                                $item['update_status'] = 1;
                                $items[] = $item;
                            }
                            $status = false;
                        }

                        // EC-CUBEのバージョンチェック
                        // 参照渡しをして値を追加
                        foreach ($items as &$item) {
                            if (in_array(Constant::VERSION, $item['eccube_version'])) {
                                // 対象バージョン
                                $item['version_check'] = 1;
                            } else {
                                // 未対象バージョン
                                $item['version_check'] = 0;
                            }
                            if ($item['price'] != '0' && $item['purchased'] == '0') {
                                // 有料商品で未購入
                                $item['update_status'] = 4;
                            }
                        }
                        unset($item);

                        // promotionアイテム
                        $i = 0;
                        foreach ($items as $item) {
                            if ($item['promotion'] == 1) {
                                $promotionItems[] = $item;
                                unset($items[$i]);
                            }
                            $i++;
                        }

                    } else {
                        $message = $data['error_code'] . ' : ' . $data['error_message'];
                    }
                } else {
                    $success = 0;
                    $message = "EC-CUBEオーナーズストアにエラーが発生しています。";
                }
            }

        } else {
            $authResult = false;
        }

        return $app->render('Store/plugin_owners_install.twig', array(
            'authResult' => $authResult,
            'success' => $success,
            'items' => $items,
            'promotionItems' => $promotionItems,
            'message' => $message,
        ));

    }

    /**
     * オーナーズブラグインインストール、アップデート
     *
     * @param Application $app
     * @param Request $request
     * @param $action
     * @param $id
     * @param $version
     */
    public function upgrade(Application $app, Request $request, $action, $id, $version)
    {

        $BaseInfo = $app['eccube.repository.base_info']->get();

        $authKey = $BaseInfo->getAuthenticationKey();
        $message = '';

        if (!is_null($authKey)) {

            // オーナーズストア通信
            $url = $app['config']['owners_store_url'] . '?method=download&product_id=' . $id;
            list($json, $httpHeader) = $this->getRequestApi($request, $authKey, $url);

            if ($json === false) {
                // 接続失敗時

                $message = $this->getResponseErrorMessage($httpHeader);

            } else {
                // 接続成功時

                $data = json_decode($json, true);

                if (isset($data['success'])) {
                    $success = $data['success'];
                    if ($success == '1') {
                        $tmpDir = null;
                        try {
                            $service = $app['eccube.service.plugin'];

                            $item = $data['item'];
                            $file = base64_decode($item['data']);
                            $extension = pathinfo($item['file_name'], PATHINFO_EXTENSION);

                            $tmpDir = $service->createTempDir();
                            $tmpFile = sha1(Str::random(32)) . '.' . $extension;

                            // ファイル作成
                            $fs = new Filesystem();
                            $fs->dumpFile($tmpDir . '/' . $tmpFile, $file);

                            if ($action == 'install') {

                                $service->install($tmpDir . '/' . $tmpFile, $id);
                                $app->addSuccess('admin.plugin.install.complete', 'admin');

                            } else if ($action == 'update') {

                                $Plugin = $app['eccube.repository.plugin']->findOneBy(array('source' => $id));

                                $service->update($Plugin, $tmpDir . '/' . $tmpFile);
                                $app->addSuccess('admin.plugin.update.complete', 'admin');
                            }

                            $fs = new Filesystem();
                            $fs->remove($tmpDir);

                            // ダウンロード完了通知処理(正常終了時)
                            $url = $app['config']['owners_store_url'] . '?method=commit&product_id=' . $id . '&status=1&version=' . $version;
                            $this->getRequestApi($request, $authKey, $url);

                            return $app->redirect($app->url('admin_store_plugin'));

                        } catch (PluginException $e) {
                            if (!empty($tmpDir) && file_exists($tmpDir)) {
                                $fs = new Filesystem();
                                $fs->remove($tmpDir);
                            }
                            $message = $e->getMessage();
                        }

                    } else {
                        $message = $data['error_code'] . ' : ' . $data['error_message'];
                    }
                } else {
                    $message = "EC-CUBEオーナーズストアにエラーが発生しています。";
                }
            }
        }

        // ダウンロード完了通知処理(エラー発生時)
        $url = $app['config']['owners_store_url'] . '?method=commit&product_id=' . $id . '&status=0&version=' . $version . '&message=' . urlencode($message);
        $this->getRequestApi($request, $authKey, $url);

        $app->addError($message, 'admin');

        return $app->redirect($app->url('admin_store_plugin_owners_install'));
    }

    /**
     * 認証キー設定画面
     *
     * @param Application $app
     * @param Request $request
     */
    public function authenticationSetting(Application $app, Request $request)
    {

        $form = $app->form()->getForm();

        $BaseInfo = $app['eccube.repository.base_info']->get();

        // 認証キーの取得
        $form->add(
            'authentication_key', 'text', array(
            'label' => '認証キー',
            'constraints' => array(
                new Assert\Regex(array(
                    'pattern' => "/^[0-9a-zA-Z]+$/",
                )),
            ),
            'data' => $BaseInfo->getAuthenticationKey(),
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                // 認証キーの登録
                $BaseInfo->setAuthenticationKey($data['authentication_key']);
                $app['orm.em']->flush($BaseInfo);

                $app->addSuccess('admin.plugin.authentication.setting.complete', 'admin');

            }
        }


        return $app->render('Store/authentication_setting.twig', array(
            'form' => $form->createView(),
        ));

    }


    /**
     * APIリクエスト処理
     *
     * @param Request $request
     * @param $authKey
     * @param $url
     * @return array
     */
    private function getRequestApi(Request $request, $authKey, $url)
    {
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'ignore_errors' => false,
                'timeout' => 60,
                'header' => array(
                    'Authorization: ' . base64_encode($authKey),
                    'x-eccube-store-url: ' . base64_encode($request->getSchemeAndHttpHost() . $request->getBasePath()),
                    'x-eccube-store-version: ' . base64_encode(Constant::VERSION)
                )
            )
        );

        $context = stream_context_create($opts);

        $json = @file_get_contents($url, false, $context);

        return array($json, $http_response_header);
    }

    /**
     * レスポンスヘッダーのチェック
     *
     * @param $httpHeader
     * @return string
     */
    private function getResponseErrorMessage($httpHeader)
    {
        if (!empty($httpHeader)) {
            list($version, $statusCode, $message) = explode(' ', $httpHeader[0], 3);

            switch ($statusCode) {
                case '404':
                    $message = $statusCode . ' : ' . $message;
                    break;
                case '500':
                    $message = $statusCode . ' : ' . $message;
                    break;
                default:
                    $message = "EC-CUBEオーナーズストアにエラーが発生しています。";
                    break;
            }
        } else {
            $message = "タイムアウトエラーまたはURLの指定に誤りがあります。";
        }

        return $message;
    }

}
