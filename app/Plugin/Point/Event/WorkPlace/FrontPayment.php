<?php


namespace Plugin\Point\Event\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : 商品購入確認
 *  - 拡張項目 : 合計金額・ポイント
 * Class FrontPayment
 * @package Plugin\Point\Event\WorkPlace
 */
class FrontPayment extends AbstractWorkPlace
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
     */
    public function createTwig(TemplateEvent $event)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * 通常はデータの保存を行うが、本処理では、合計金額判定とエラー処理
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        // 必要エンティティの確認
        if (!$event->hasArgument('Order')) {
            return false;
        }
        $order = $event->getArgument('Order');

        // 会員情報を取得
        $customer = $order->getCustomer();
        if (empty($customer)) {
            return false;
        }

        // 計算用ヘルパー呼び出し
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];
        // 計算ヘルパー取得判定
        if (empty($calculator)) {
            return false;
        }

        // 計算に必要なエンティティを登録
        $calculator->addEntity('Order', $order);
        $calculator->addEntity('Customer', $customer);

        // 合計金額マイナス確認
        if ($calculator->isTotalAsMinus()) {
            $this->app->addError('お支払い金額がマイナスになったため、ポイントをキャンセルしました。', 'front.request');
        }
    }
}
