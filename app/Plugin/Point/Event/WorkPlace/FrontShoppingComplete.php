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
 *  - 拡張元 : 商品購入完了
 *  - 拡張項目 : メール内容
 * Class FrontShoppingComplete
 * @package Plugin\Point\Event\WorkPlace
 */
class FrontShoppingComplete extends AbstractWorkPlace
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

    //
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
     * 本クラスでは処理なし
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * ポイントログの保存
     *  - 仮付与ポイント
     *  - 確定ポイント判定
     *  - スナップショット保存
     *  - メール送信
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        // オーダー判定
        $orderId = $event->getArgument('orderId');
        if(empty($orderId)){
            return false;
        }

        // 受注情報の取得判定
        $order = $this->app['eccube.repository.order']->findOneById($orderId);
        if (empty($order)) {
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
        $calculator->addEntity('Customer', $order->getCustomer());
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

        // ポイント付与受注ステータスが「新規」の場合、付与ポイントを確定
        $add_point_flg = false;
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        // ポイント機能基本設定の付与ポイント受注ステータスを取得
        if ($pointInfo->getPlgAddPointStatus() == $this->app['config']['order_new']) {
            $add_point_flg = true;
        }

        // 履歴情報登録
        // 利用ポイント
        /*
        $this->app['eccube.plugin.point.history.service']->addEntity($order);
        $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
        $this->app['eccube.plugin.point.history.service']->saveUsePoint($usePoint);
        */

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

        // 支払い合計金額更新
        // @todo 値引き額での計算に変更が必要
        //$order->setPaymentTotal($amount);
        //$order->setTotal($amount);
        /*
        $revDiscount = $order->getDiscount();

        if(empty($revDiscount)){
            $order->setDiscount($point['use']);
        }else{
            $order->setDiscount(($revDiscount + $point['use']));
        }

        //$order->setDiscount();
        $this->app['orm.em']->persist($order);
        $this->app['orm.em']->flush($order);


        // 利用ポイントクリア
        if ($this->app['session']->has('usePoint')) {
            $this->app['session']->remove('usePoint');
        }
        */

        // //イベントを取得後、Orderを取得
        // 取得Orderをもとに情報を保存
        // もともと確認画面で使用されていたロジックを流用
        // 該当受注関連はステータスが「8」以外のものを取得すること
        // 取得情報からログ保存
        // 取得情報からSnapShot保存上記完了後に実装を終了
        // 課題: メールの送信
        // 課題: メール履歴の保存
        // GMO完了のタイミングが現在不明
        // 完了タイミングが確定してから本実装
        // ポイントログの保存
        // 基本情報の取得
        /*
        $order = $event->getArgument('Order');
        $mailHistory = $event->getArgument('MailHistory');

        // 必要情報判定
        if (empty($order)) {
            return false;
        }

        if (empty($order->getCustomer())) {
            return false;
        }

        // 計算ヘルパーの取得
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 利用ポイントの取得と設定
        $usePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($order);
        if (empty($usePoint)) {
            $usePoint = 0;
        }

        $calculator->setUsePoint($usePoint);

        // 計算に必要なエンティティの設定
        $calculator->addEntity('Order', $order);
        $calculator->addEntity('Customer', $order->getCustomer());

        // 計算値取得
        $addPoint = $calculator->getAddPointByOrder();

        // ポイント配列作成
        $pointMessage = array();
        $pointMessage['add'] = $addPoint;
        $pointMessage['use'] = 0 - $usePoint;

        // メールボディ取得
        $body = $mailHistory->getMailBody();

        // 情報置換用のキーを取得
        $search = array();
        preg_match_all('/合　計 ¥ .*\\n/u', $body, $search);

        // メール本文置換
        //$snipet = $this->createPointMailMessage($pointMessage);
        $snipet = 'ご利用ポイント :'.$pointMessage['use'].PHP_EOL;
        $replace = $snipet.$search[0][0];
        $body = preg_replace('/'.$search[0][0].'/u', $replace, $body);

        $snipet2 = PHP_EOL;
        $snipet2 .= PHP_EOL;
        $snipet2 .= '***********************************************'.PHP_EOL;
        $snipet2 .= '　ポイント情報                                 '.PHP_EOL;
        $snipet2 .= '***********************************************'.PHP_EOL;
        $snipet2 .= '加算ポイント :'.$pointMessage['add'].PHP_EOL;
        $snipet2 .= PHP_EOL;
        $replace = $search[0][0].$snipet2;
        $body = preg_replace('/'.$search[0][0].'/u', $replace, $body);
        // メッセージにメールボディをセット
        $mailHistory->setMailBody($body);

        $this->app['orm.em']->persist($mailHistory);
        $this->app['orm.em']->flush($mailHistory);
        */
    }
}
