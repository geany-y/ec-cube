<?php

namespace Plugin\Point;

use Eccube\Application;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use HttpException\NotFoundHttpException;


/**
 * ポイントプラグインイベント処理ルーティングクラス
 * Class PointEvent
 * @package Plugin\Point
 */
class PointEvent
{
    // ヘルパー呼び出し用
    // 管理画面
    const HELPER_ADMIN_PRODUCT = 'AdminProduct';
    const HELPER_ADMIN_CUSTOMER = 'AdminCustomer';
    const HELPER_ADMIN_ORDER = 'AdminOrder';

    // フロント画面
    const HELPER_FRONT_SHOPPING = 'FrontShopping';
    const HELPER_FRONT_SHOPPING_INDEX = 'FrontShoppingIndex';
    const HELPER_FRONT_SHOPPING_CONFIRM = 'FrontShoppingConfirm';
    const HELPER_FRONT_SHOPPING_COMPLETE = 'FrontShoppingComplete';
    const HELPER_FRONT_MYPAGE = 'FrontMypage';
    const HELPER_FRONT_PRODUCT_DETAIL = 'FrontProductDetail';
    const HELPER_FRONT_CART = 'FrontCart';
    const HELPER_FRONT_HISTORY = 'FrontHistory';

    // サービス
    const HELPER_SERVICE_MAIL = 'ServiceMail';


    /** @var  \Eccube\Application $app */
    protected $app;
    /** @var null */
    protected $factory = null;
    /** @var null */
    protected $helper = null;

    /**
     * PointEvent constructor.
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 商品毎ポイント付与率
     *  - フォーム拡張処理
     *  - 管理画面 > 商品編集
     * @param EventArgs $event
     */
    public function onAdminProductEditInitialize(EventArgs $event)
    {
        // フックポイント汎用処理サービス取得 ( 商品登録編集画面用/初期化 )
        $this->setHelper(self::HELPER_ADMIN_PRODUCT);

        //フォーム拡張
        $this->createForm($event);
    }

    /**
     * 商品毎ポイント付与率
     *  - 保存処理
     *  - 管理画面 > 商品編集
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs $event)
    {
        // フックポイント汎用処理サービス取得 ( 商品登録編集画面用/終了時 )
        $this->setHelper(self::HELPER_ADMIN_PRODUCT);

        // ポイント付与率保存処理
        $this->save($event);
    }

    /**
     * 会員保有ポイント
     *  - フォーム拡張処理
     *  - 管理画面 > 会員編集
     * @param EventArgs $event
     */
    public function onAdminCustomerEditIndexInitialize(EventArgs $event)
    {
        // フックポイント汎用処理サービス取得 ( 会員登録編集画面用/初期化 )
        $this->setHelper(self::HELPER_ADMIN_CUSTOMER);

        //フォーム拡張
        $this->createForm($event);
    }

    /**
     * 会員保有ポイント
     *  - 保存処理
     *  - 管理画面 > 会員編集
     * @param EventArgs $event
     */
    public function onAdminCustomerEditIndexComplete(EventArgs $event)
    {
        // フックポイント汎用処理サービス取得 ( 会員登録編集画面用/終了時 )
        $this->setHelper(self::HELPER_ADMIN_CUSTOMER);

        // ポイント付与率保存処理
        $this->save($event);
    }

    /**
     * 受注ステータス登録・編集
     *  - フォーム項目追加
     *  - 管理画面 > 受注登録 ( 編集 )
     * @param EventArgs $event
     */
    public function onAdminOrderEditIndexInitialize(EventArgs $event)
    {
        // フックポイント汎用処理サービス取得 ( 会員登録編集画面用/初期化 )
        $this->setHelper(self::HELPER_ADMIN_ORDER);

        // ポイント付与率保存処理
        $this->createForm($event);
    }

    /**
     * 受注ステータス変更時ポイント付与
     *  - 判定・更新処理
     *  - 更新処理前
     *  - 管理画面 > 受注登録 ( 編集 )
     * @param EventArgs $event
     */
    // @todo need delete
    public function onAdminOrderEditIndexProcessing(EventArgs $event)
    {
        // フックポイント汎用処理サービス取得 ( 会員登録編集画面用/終了 )
        $this->setHelper(self::HELPER_ADMIN_ORDER);

        // ポイント付与率保存処理
        $this->save($event);
    }

    /**
     * 受注ステータス変更時ポイント付与
     *  - 判定・更新処理
     *  - 管理画面 > 受注登録 ( 編集 )
     * @param EventArgs $event
     */
    public function onAdminOrderEditIndexComplete(EventArgs $event)
    {
        // フックポイント汎用処理サービス取得 ( 会員登録編集画面用/終了 )
        $this->setHelper(self::HELPER_ADMIN_ORDER);

        // ポイント付与率保存処理
        $this->save($event);
    }

    /*
    public function onFrontShoppingIndexInitialize(EventArgs $event){
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント定形処理ヘルパー取得 ( 商品購入確認/初期処理 )
        $this->setHelper(self::HELPER_FRONT_SHOPPING_INDEX);

        // ポイント関連保存処理
        $this->save($event);
    }
    */

    /**
     * 商品購入確認完了
     *  - 利用ポイント・保有ポイント・仮付与ポイント保存
     *  - フロント画面 > 商品購入確認完了
     * @param EventArgs $event
     * @return bool
     */
    /*
    public function onFrontShoppingConfirmProcessing(EventArgs $event)
    {
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント定形処理ヘルパー取得 ( 商品購入完了/処理途中 )
        $this->setHelper(self::HELPER_FRONT_SHOPPING_CONFIRM);

        // ポイント関連保存処理
        $this->save($event);
    }
    */

    /**
     * 商品購入確認完了
     *  - 利用ポイント・保有ポイント・仮付与ポイントメール反映
     *  - フロント画面 > 商品購入確認完了
     * @param EventArgs $event
     * @return bool
     */
    /*
    public function onFrontShoppingConfirmComplete(EventArgs $event)
    {
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント定形処理ヘルパー取得 ( 商品購入完了/終了 )
        $this->setHelper(self::HELPER_FRONT_SHOPPING_COMPLETE);

        // ポイント関連保存処理
        $this->save($event);
    }
    */

    /**
     * 購入完了画面
     *  - 仮付与ポイントポイントログ登録
     *  - 利用ポイントログ登録
     *  - 保有ポイントログ処理
     *  - フロント画面 > 商品購入完了画面
     * @param TemplateEvent $event
     * @return bool
     */
    public function onFrontShoppingCompleteInitialize(EventArgs $event){
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント汎用処理サービス取得 ( 商品購入完了画面用 )
        $this->setHelper(self::HELPER_FRONT_SHOPPING_COMPLETE);

        // ポイント関連保存処理
        $this->save($event);
    }

    /**
     * 商品購入確認画面
     *  - ポイント使用処理
     *  - 付与ポイント計算処理・画面描画処理
     *  - フロント画面 > 商品購入確認画面
     * @param TemplateEvent $event
     * @return bool
     */
    public function onRenderShoppingIndex(TemplateEvent $event)
    {
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント汎用処理サービス取得 ( 商品購入画面用 )
        $this->setHelper(self::HELPER_FRONT_SHOPPING);

        // Twig拡張(ポイント計算/合計金額計算・描画)
        $this->createTwig($event);
    }


    /**
     * 管理画面受注編集
     *  - 利用ポイント・保有ポイント・付与ポイント表示
     *  - 管理画面 > 受注情報登録・編集
     * @param TemplateEvent $event
     */
    public function onRenderAdminOrderEdit(TemplateEvent $event)
    {
        $args = $event->getParameters();

        // フックポイント定形処理ヘルパー取得 ( 商品購入完了 )
        $this->setHelper(self::HELPER_ADMIN_ORDER);

        // ポイント関連保存処理
        $this->createTwig($event);

    }

    /**     * マイページ
     *  - 利用ポイント・保有ポイント・仮付与ポイント保存
     * @param TemplateEvent $event
     * @return bool
     */
    public function onRenderMyPageIndex(TemplateEvent $event)
    {
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント定形処理ヘルパー取得 ( 商品購入完了 )
        $this->setHelper(self::HELPER_FRONT_MYPAGE);

        // ポイント関連保存処理
        $this->createTwig($event);
    }

    /**
     * 商品購入完了メール
     *  - ポイントの表示
     * @param EventArgs $event
     * @return bool
     */
    public function onMailOrderComplete(EventArgs $event)
    {
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント定形処理ヘルパー取得 ( 受注完了/終了 )
        $this->setHelper(self::HELPER_SERVICE_MAIL);

        // ポイント関連保存処理
        $this->save($event);
    }

    /**
     * 商品詳細画面
     *  - 付与ポイント表示
     * @param TemplateEvent $event
     */
    public function onRenderProductDetail(TemplateEvent $event)
    {
        // フックポイント定形処理ヘルパー取得 ( 商品詳細 )
        $this->setHelper(self::HELPER_FRONT_PRODUCT_DETAIL);

        // ポイント関連保存処理
        $this->createTwig($event);
    }

    /**
     * カート画面
     *  - 利用ポイント・保有ポイント・仮付与ポイント表示
     * @param TemplateEvent $event
     */
    public function onRenderCart(TemplateEvent $event)
    {
        // フックポイント定形処理ヘルパー取得 ( カート画面 )
        $this->setHelper(self::HELPER_FRONT_CART);

        // ポイント関連保存処理
        $this->createTwig($event);
    }

    /**
     * マイページ履歴画面
     *  - 利用ポイント・保有ポイント・仮付与ポイント表示
     * @param TemplateEvent $event
     * @return bool
     */
    public function onRenderHistory(TemplateEvent $event)
    {
        // ログイン判定
        if (!$this->isAuthRouteFront()) {
            return true;
        }

        // フックポイント定形処理ヘルパー取得 ( マイページ履歴 )
        $this->setHelper(self::HELPER_FRONT_HISTORY);

        // ポイント関連保存処理
        $this->createTwig($event);
    }

    /**
     * 管理画面権確認
     * @throws NotFoundHttpException
     */
    protected function isAuthRouteAdmin()
    {
        // 権限判定
        if (!$this->app->isGranted('ROLE_ADMIN') && !$this->app->isGranted('ROLE_USER')) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        return;
    }

    /**
     * フロント画面権限確認
     * @return bool
     */
    protected function isAuthRouteFront()
    {
        // 権限判定
        if (!$this->app->isGranted('IS_AUTHENTICATED_FULLY')) {
            return false;
        }

        return true;
    }

    /**
     * ヘルパーインスタンス取得処理呼び出しラッパーメソッド
     * @param $key
     */
    protected function setHelper($key)
    {
        $this->factory = $this->app['eccube.plugin.point.hookpoint.routinework.helper.factory'];
        // フックポイント汎用処理ヘルパー取得
        $this->helper = $this->factory->createEventRoutineWorksHelper($key);

        return;
    }

    /**
     * ヘルパー機能フォーム作成呼び出しラッパーメソッド
     * @param EventArgs $event
     * @return mixed
     */
    protected function createForm(EventArgs $event)
    {
        /** @var \Symfony\Component\Form\FormBuilder $formBuilder */
        // フォームビルダー取得
        return $this->helper->createForm($event->getArgument('builder'), $this->app['request']);
    }

    /**
     * ヘルパー機能保存処理呼び出しラッパーメソッド
     * @param EventArgs $event
     * @return mixed
     */
    protected function save(EventArgs $event)
    {
        return $this->helper->save($event);
    }

    /**
     * ヘルパー機能Twig作成呼び出しラッパーメソッド
     * @param TemplateEvent $event
     * @return mixed
     */
    protected function createTwig(TemplateEvent $event)
    {
        return $this->helper->createTwig($event);
    }
}
