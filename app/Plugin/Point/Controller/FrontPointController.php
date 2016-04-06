<?php

namespace Plugin\Point\Controller;

use Eccube\Application;
use Plugin\Point\Form\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PointController
 * ポイント設定画面用コントローラー
 * Class FrontPointController
 * @package Plugin\Point\Controller
 */
class FrontPointController
{
    /** @var Application */
    protected $app;

    /**
     * FrontPointController constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * 利用ポイント入力画面
     * @param Application $app
     * @param Request $request
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function usePoint(Application $app, Request $request)
    {
        // 権限判定
        if (!$this->app->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new HttpException\NotFoundHttpException;
        }

        // カートサービス取得
        $cartService = $this->app['eccube.service.cart'];

        // カートチェック
        if (!$cartService->isLocked()) {
            // カートが存在しない、カートがロックされていない時はエラー
            return $this->app->redirect($this->app->url('cart'));
        }

        // 受注情報が取得される
        $Order = $this->app['eccube.service.shopping']->getOrder($this->app['config']['order_processing']);

        // 受注情報がない場合はエラー表示
        if (!$Order) {
            $this->app->addError('front.shopping.order.error');

            return $this->app->redirect($this->app->url('shopping_error'));
        }

        // 最終仮保存のポイント設定情報取得
        // 仮利用ポイントの保存処理
        $usePoint = 0;
        // 最終仮保存ポイントがあるかどうかの判定
        $lastPreUsePoint = $this->app['eccube.plugin.point.repository.point']->getLastPreUsePoint($Order);


        if (!empty($lastPreUsePoint)) {
            $usePoint = $lastPreUsePoint;
        }
        /*
        if () {
            $usePoint = $this->app['session']->get('usePoint');
        }
        */

        // 必要エンティティ取得
        // カスタマーエンティティ
        $customer = $this->app['security']->getToken()->getUser();

        // 計算用ヘルパー呼び出し
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 計算ヘルパー取得判定
        if (empty($calculator)) {
            return false;
        }

        // 利用ポイントをセット
        $calculator->setUsePoint($usePoint);
        // 計算に必要なエンティティを格納
        $calculator->addEntity('Customer', $customer);
        $calculator->addEntity('Order', $Order);

        // 保有ポイント
        $point = $calculator->getPoint();
        // 加算ポイント
        $addPoint = $calculator->getAddPointByOrder();

        // 合計金額
        // @todo 本部分ロジックの変更必要
        // サービスで取得
        //$newOrder = $calculator->getTotalAmount();



        // ポイント換算レート
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        if (empty($pointInfo)) {
            return false;
        }
        $pointRate = $pointInfo->getPlgBasicPointRate();

        //フォーム生成
        $form = $this->app['form.factory']
            ->createBuilder()->add(
                'plg_use_point',
                'text',
                array(
                    'label' => '利用ポイント',
                    'required' => false,
                    'mapped' => false,
                    'empty_data' => '',
                    'data' => $usePoint,
                    'attr' => array(
                        'placeholder' => '使用するポイントを入力 例. 1',
                    ),
                    'constraints' => array(
                        new Assert\LessThanOrEqual(
                            array(
                                'value' => $point,
                                'message' => 'front.point.enter.usepointe.error',
                            )
                        ),
                        new Assert\Regex(
                            array(
                                'pattern' => "/^\d+$/u",
                                'message' => 'form.type.numeric.invalid',
                            )
                        ),
                    ),
                )
            )->getForm();

        $form->handleRequest($request);

        // 保存処理
        if ($form->isSubmitted() && $form->isValid()) {
            // ユーザー入力値
            $saveUsePoint = $form->get('plg_use_point')->getData();

            // 最終保存ポイントがあるかどうかの判定
            $lastPreUsePoint = 0;
            $lastPreUsePoint = $this->app['eccube.plugin.point.repository.point']->getLastPreUsePoint($Order);
            if (!empty($lastPreUsePoint)) {
                $usePoint = $lastPreUsePoint;
            }

            // 最終保存ポイントと現在ポイントに相違があれば利用ポイント保存
            if (abs($lastPreUsePoint) != abs($saveUsePoint)) {
                // 履歴情報登録
                // 利用ポイント
                // 再入力時は、以前利用ポイントを打ち消し
                if (!empty($lastPreUsePoint)) {
                    $this->app['eccube.plugin.point.history.service']->addEntity($Order);
                    $this->app['eccube.plugin.point.history.service']->addEntity($Order->getCustomer());
                    $this->app['eccube.plugin.point.history.service']->savePreUsePoint(abs($lastPreUsePoint));
                }
                // ユーザー入力値保存
                $this->app['eccube.plugin.point.history.service']->refreshEntity();
                $this->app['eccube.plugin.point.history.service']->addEntity($Order);
                $this->app['eccube.plugin.point.history.service']->addEntity($Order->getCustomer());
                $this->app['eccube.plugin.point.history.service']->savePreUsePoint(abs($saveUsePoint) * -1);

                // 現在ポイントを履歴から計算
                $calculateCurrentPoint = $this->app['eccube.plugin.point.repository.point']->getCalculateCurrentPointByCustomerId(
                    $Order->getCustomer()->getId()
                );

                // 会員ポイント更新
                $this->app['eccube.plugin.point.repository.pointcustomer']->savePoint(
                    $calculateCurrentPoint,
                    $Order->getCustomer()
                );

                $calculator->setUsePoint($saveUsePoint);
                $calculator->setDiscount($lastPreUsePoint);
                $newOrder = $calculator->getEntity('Order');

                // 値引き計算後のオーダーが返却
                $this->app['eccube.service.shopping']->getAmount($newOrder);
            }

            return $this->app->redirect($this->app->url('shopping'));
        }

        // フォーム項目名称描画
        // ポイント利用画面描画
        /**
         * フォーム→ビルダー
         * 利用ポイントが毎回引き当てOR入力値
         * 換算レート→ポイント基本設定値から取得
         * 保有ポイント→再計算処理後に取得
         * 合計金額→ShoppingService から取得
         *
         */
        $total = $Order->getPaymentTotal();
        /*
        if (!empty($newOrder)) {
            $total = $newOrder->getPaymentTotal();
        }
        */

        return $app->render(
            'Point/Resource/template/default/point_use.twig',
            array(
                'form' => $form->createView(),  // フォーム
                'usePoint' => $usePoint,        // 利用ポイント
                'pointRate' => $pointRate,      // 換算レート
                'point' => $point - $usePoint,              // 保有ポイント
                'total' => $total,              // 合計金額
            )
        );
    }
}
