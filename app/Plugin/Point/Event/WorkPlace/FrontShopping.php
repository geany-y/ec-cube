<?php


namespace Plugin\Point\Event\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\Point\Entity\PointUse;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : 商品購入確認
 *  - 拡張項目 : 合計金額・ポイント
 * Class FrontShopping
 * @package Plugin\Point\Event\WorkPlace
 */
class FrontShopping extends AbstractWorkPlace
{
    /**
     * 本クラスでは処理なし
     * @param FormBuilder $builder
     * @param Request $request
     */
    public function createForm(FormBuilder $builder, Request $request)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * 本クラスでは処理なし
     * @param Request $request
     * @param Response $response
     */
    public function renderView(Request $request, Response $response)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * フロント商品購入確認画面
     * - ポイント計算/購入金額合計計算
     * @param TemplateEvent $event
     * @return bool
     */
    public function createTwig(TemplateEvent $event)
    {
        $args = $event->getParameters();

        $order = $args['Order'];

        // オーダーエンティティの確認
        if (empty($order)) {
            return false;
        }

        $customer = $order->getCustomer();

        // カスタマーエンティティ判定
        if (empty($customer)) {
            return false;
        }

        // 計算判定取得
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 計算ヘルパー取得判定
        if (is_null($calculator)) {
            return true;
        }

        // 利用ポイントの確認
        // @todo ポイント取得方法の変更
        $pointUse = new PointUse();
        $usePoint = 0;
        $lastPreUsePoint = 0;
        $lastPreUsePoint = $this->app['eccube.plugin.point.repository.point']->getLastPreUsePoint($order);
        if (!empty($lastPreUsePoint)) {
            $usePoint = $lastPreUsePoint;
        }

        // 計算に必要なエンティティを登録
        $calculator->setUsePoint($usePoint);
        $calculator->addEntity('Order', $order);
        $calculator->addEntity('Customer', $customer);

        // 付与ポイント取得
        $addPoint = $calculator->getAddPointByOrder();

        //付与ポイント取得可否判定
        if (is_null($addPoint)) {
            return true;
        }

        // 現在保有ポイント取得
        $currentPoint = $calculator->getPoint();

        //保有ポイント取得可否判定
        if (is_null($currentPoint)) {
            $currentPoint = 0;
        }


        //$calculator->setDiscount($lastUsePoint);
        //$setUsePointOrder = $calculator->getEntity('Order');

        // 値引き計算後のオーダーが返却
        //$newOrder = $this->app['eccube.service.shopping']->getAmount($setUsePointOrder);
        //$calculator->removeEntity('Order');
        //$calculator->addEntity('Order', $newOrder);

        // ビュー返却値の受注情報を値引き後の情報に更新
        //$args['Order'] = $newOrder;
        //$event->setParameters($args);

        // ポイント使用合計金額取得・設定
        // @todo 値引き再計算Orderの取得
        //$amount = $calculator->getTotalAmount();

        // 合計金額をセット
        //$order->setTotal($amount);
        //$order->getDiscount();

        // ポイント基本情報を取得
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        // ポイント表示用変数作成
        $point = array();
        $point['current'] = $currentPoint - $usePoint;
        // エラー判定
        // false が返却された際は、利用ポイント値が保有ポイント値を超えている
        /*
        if ($amount == false) {
            $point['use_error'] = 'front.point.display.usepointe.error';
        } else {
            $point['use'] = 0 - $usePoint;
        }
        */
        $point['use'] = 0;
        if(!empty($usePoint)) {
            $point['use'] = abs($usePoint);
        }
        $point['add'] = $addPoint;
        $point['rate'] = $pointInfo->getPlgBasicPointRate();

        // Twigデータ内IDをキーに表示項目を追加
        // ポイント情報表示
        // false が返却された際は、利用ポイント値が保有ポイント値を超えている
        /*
        if ($amount == false) {
            $snippet = $this->app->render(
                'Point/Resource/template/default/Event/ShoppingConfirm/point_summary_error.twig',
                array(
                    'point' => $point,
                )
            )->getContent();
        } else {
        */
            $snippet = $this->app->render(
                'Point/Resource/template/default/Event/ShoppingConfirm/point_summary.twig',
                array(
                    'point' => $point,
                )
            )->getContent();
        //}
        $search = '<p id="summary_box__total_amount"';
        $this->replaceView($event, $snippet, $search);

        // 使用ポイントボタン付与
        // twigコードに利用ポイントを挿入
        $snippet = $this->app->render(
            'Point/Resource/template/default/Event/ShoppingConfirm/use_point_button.twig',
            array(
                'point' => $point,
            )
        )->getContent();
        $search = '<h2 class="heading02">お問い合わせ欄</h2>';
        $this->replaceView($event, $snippet, $search);
    }

    /**
     * 通常はデータの保存を行うが、本処理では、情報の取得のみ
     * @param EventArgs $event
     */
    // @todo 購入商品確認画面から処理を移設
    public function save(EventArgs $event)
    {
        /*
        // 保存対象受注情報の取得
        $args = $event->getArguments();
        $order = $args['Order'];

        // 受注判定
        if (empty($order)) {
            return false;
        }

        $customer = $order->getCustomer();
        // カスタマー情報判定
        if (empty($customer)) {
            return false;
        }

        // 使用ポイントをエンティティに格納
        $pointUse = new PointUse();
        $usePoint = 0;

        // 最終保存ポイントがあるかどうかの判定
        $lastUsePoint = 0;
        $lastUsePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($order);
        if (!empty($lastUsePoint)) {
            $usePoint = $lastUsePoint;
        }

        // 計算判定取得
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 計算ヘルパー取得判定
        if (is_null($calculator)) {
            // 画面がないためエラーをスロー
            throw new UndefinedFunctionException();
        }

        // 計算に必要なエンティティを登録
        $calculator->addEntity('Order', $order);
        $calculator->addEntity('Customer', $customer);
        $calculator->setUsePoint($usePoint);

        // 付与ポイント取得
        $addPoint = $calculator->getAddPointByOrder();

        //付与ポイント取得可否判定
        if (is_null($addPoint)) {
            // 画面がないためエラーをスロー
            throw new \UnexpectedValueException();
        }

        // 現在保有ポイント取得
        $currentPoint = $calculator->getPoint();

        //保有ポイント取得可否判定
        if (is_null($currentPoint)) {
            $currentPoint = 0;
        }

        // ポイント使用合計金額取得・設定
        // @todo 以下処理は全て決済処理完了後イベントで処理

        //$calculator->setDiscount($lastUsePoint);
        //$newOrder = $calculator->getEntity('Order');

        //dump($newOrder);
        //exit();

        // 値引き計算後のオーダーが返却
        //$this->app['eccube.service.shopping']->getAmount($newOrder);

        //$calculator->removeEntity('Order');
        //$calculator->addEntity('Order', $newOrder);


        //$args = $event->getArguments();
        //$order = $args['Order'];
        */

        // ポイント付与受注ステータスが「新規」の場合、付与ポイントを確定
        /*
        $add_point_flg = false;
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        // ポイント機能基本設定の付与ポイント受注ステータスを取得
        if ($pointInfo->getPlgAddPointStatus() == $this->app['config']['order_new']) {
            $add_point_flg = true;
        }

        // 履歴情報登録
        // 利用ポイント
        $this->app['eccube.plugin.point.history.service']->addEntity($order);
        $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
        $this->app['eccube.plugin.point.history.service']->saveUsePoint(abs($usePoint) * -1);

        // 仮付与ポイント(ステータスの設定により付与ポイント)
        $this->app['eccube.plugin.point.history.service']->refreshEntity();
        $this->app['eccube.plugin.point.history.service']->addEntity($order);
        $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
        $this->app['eccube.plugin.point.history.service']->saveProvisionalAddPoint($addPoint);


        // 付与ポイント受注ステータスが新規であれば、ポイント付与
        if ($add_point_flg) {
            $this->app['eccube.plugin.point.history.service']->refreshEntity();
            $this->app['eccube.plugin.point.history.service']->addEntity($order);
            $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
            $this->app['eccube.plugin.point.history.service']->fixShoppingProvisionalAddPoint($addPoint);
            $this->app['eccube.plugin.point.history.service']->refreshEntity();
            $this->app['eccube.plugin.point.history.service']->addEntity($order);
            $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
            $this->app['eccube.plugin.point.history.service']->saveShoppingFixProvisionalAddPoint($addPoint);
        }

        // 現在ポイントを履歴から計算
        $calculateCurrentPoint = $this->app['eccube.plugin.point.repository.point']->getCalculateCurrentPointByCustomerId(
            $order->getCustomer()->getId()
        );

        // 会員ポイント更新
        $this->app['eccube.plugin.point.repository.pointcustomer']->savePoint(
            $calculateCurrentPoint,
            $order->getCustomer()
        );

        // ポイント保存用変数作成
        $point = array();
        $point['current'] = $calculateCurrentPoint;
        $point['use'] = abs($usePoint) * -1;
        $point['add'] = $addPoint;
        $this->app['eccube.plugin.point.history.service']->refreshEntity();
        $this->app['eccube.plugin.point.history.service']->addEntity($order);
        $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
        $this->app['eccube.plugin.point.history.service']->saveSnapShot($point);
        */

        // 支払い合計金額更新
        // @todo 値引き額での計算に変更が必要 → 予定
        //$order->setPaymentTotal($amount);
        //$order->setTotal($amount);
        /*
        $revDiscount = $order->getDiscount();

        if(empty($revDiscount)){
            $order->setDiscount($point['use']);
        }else{
            $order->setDiscount(($revDiscount + $point['use']));
        }
        */


        //$this->app['orm.em']->persist($order);
        //$this->app['orm.em']->flush($order);



        // 利用ポイントクリア
        // 本箇所でセッションのクリアを行うと表示値が保持されない
        /*
        if ($this->app['session']->has('usePoint')) {
            $this->app['session']->remove('usePoint');
        }
        */
    }
}
