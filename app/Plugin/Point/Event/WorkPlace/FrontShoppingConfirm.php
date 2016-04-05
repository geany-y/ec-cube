<?php


namespace Plugin\Point\Event\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\Point\Entity\PointUse;
use Symfony\Component\Debug\Exception\UndefinedFunctionException;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : 商品購入確認完了
 *  - 拡張項目 : 履歴データ・ポイント
 * Class FrontShoppingConfirm
 * @package Plugin\Point\Event\WorkPlace
 */
class FrontShoppingConfirm extends AbstractWorkPlace
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
     * 本クラスでは処理なし
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * ポイントデータの保存
     * @param EventArgs $event
     * @return bool
     * @throws UndefinedFunctionException
     */
    // @todo 以下処理を仮受注情報に移設
    public function save(EventArgs $event)
    {
        throw new MethodNotAllowedException();
        /*
        // 保存対象受注情報の取得
        $args = $event->getArguments();
        $order = $args['Order'];

        if (empty($order)) {
            return false;
        }

        // 使用ポイントをエンティティに格納
        $pointUse = new PointUse();
        $usePoint = 0;
        if ($this->app['session']->has('usePoint')) {
            $usePoint = $this->app['session']->get('usePoint');
            $pointUse->setPlgUsePoint($usePoint);
            //$this->app['session']->remove('usePoint');
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

        // ポイント使用合計金額取得・設定
        // @todo 受注の支払い総額は操作せず、値引き額に対して増減→計算処理を行うこと
        $amount = $calculator->getTotalAmount();

        // ポイント付与受注ステータスが「新規」の場合、付与ポイントを確定
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
        $this->app['eccube.plugin.point.history.service']->saveUsePoint((0 - $usePoint));

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
        $point['use'] = 0 - $usePoint;
        $point['add'] = $addPoint;
        $this->app['eccube.plugin.point.history.service']->refreshEntity();
        $this->app['eccube.plugin.point.history.service']->addEntity($order);
        $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
        $this->app['eccube.plugin.point.history.service']->saveSnapShot($point);

        // 支払い合計金額更新
        // @todo 値引き額での計算に変更が必要
        //$order->setPaymentTotal($amount);
        //$order->setTotal($amount);
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
    }
}
