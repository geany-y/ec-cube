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


namespace Plugin\Point\Resource\lib\EventRoutineWorksHelper\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\Point\Entity\PointInfo;
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
     * @todo ロジックが送品購入画面とほぼ同内容
     * @todo データの引き渡しをセッションで検討
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        // 保存対象受注情報の取得
        $args = $event->getArguments();
        $order = $args['Order'];

        if(empty($order)){
            return false;
        }

        // 使用ポイントをエンティティに格納
        $pointUse = new PointUse();
        $usePoint = 0;
        if ($this->app['session']->has('usePoint')) {
            $usePoint = $this->app['session']->get('usePoint');
            $pointUse->setPlgUsePoint($usePoint);
            $this->app['session']->remove('usePoint');
        }


        // ポイント基本設定項目の取得
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();

        if(empty($pointInfo)){
            return false;
        }

        // 計算種別判定 ( 利用ポイント減算 あり/なし )
        $calcType = null;
        $calculationService = null;
        if ($pointInfo->getPlgCalculationType() == PointInfo::POINT_CALCULATE_SUBTRACTION) {
            // 利用ポイント減算処理
            $calcType = PointInfo::POINT_CALCULATE_SUBTRACTION;
        } else {
            if ($pointInfo->getPlgCalculationType() == PointInfo::POINT_CALCULATE_NORMAL) {
                // 利用ポイント減算なし
                $calcType = PointInfo::POINT_CALCULATE_NORMAL;
            }
        }

        // 計算処理設定値有無確認
        if (is_null($calcType)) {
            throw new \UnexpectedValueException();
        }

        // 計算判定取得
        $calculationService = $this->app['eccube.plugin.point.calculate.helper.factory']->createCalculateHelperFunction(
            $calcType
        );

        // 計算ヘルパー取得判定
        if (is_null($calculationService)) {
            // 画面がないためエラーをスロー
            throw new UndefinedFunctionException();
        }

        // 計算に必要なエンティティを登録
        $calculationService->addEntity($order);
        $calculationService->addEntity($order->getCustomer());
        $calculationService->addEntity($pointInfo);
        $calculationService->addEntity($pointUse);

        // ポイント付与率設定
        $rate_check = $calculationService->attributePointRate();

        //ポイント付与率設定可否判定
        if (is_null($rate_check)) {
            // 画面がないためエラーをスロー
            throw new \UnexpectedValueException();
        }

        // 付与ポイント取得
        $addPoint = $calculationService->getAddPoint();


        //付与ポイント取得可否判定
        if (is_null($addPoint)) {
            // 画面がないためエラーをスロー
            throw new \UnexpectedValueException();
        }


        // 現在保有ポイント取得 @todo ポイント取得は履歴からの再計算が必要??
        $currentPoint = $calculationService->getPoint();

        //保有ポイント取得可否判定
        if (is_null($currentPoint)) {
            $currentPoint = 0;
        }

        // ポイント使用合計金額取得・設定
        $amount = $calculationService->getTotalAmount();

        // ポイント付与受注ステータスが「新規」の場合、付与ポイントを確定
        $add_point_flg = false;
        // ポイント機能基本設定の付与ポイント受注ステータスを取得
        if ($pointInfo->getPlgAddPointStatus() == $this->app['config']['order_new']) {
            $add_point_flg  = true;
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

        // 現在ポイントを履歴から計算
        $calculateCurrentPoint = $this->app['eccube.plugin.point.repository.point']->getCalculateCurrentPointByCustomerId(
            $order->getCustomer()->getId()
        );

        // 付与ポイント受注ステータスが新規であれば、ポイント付与
        if ($add_point_flg) {
            $this->app['eccube.plugin.point.history.service']->saveAddPoint($addPoint);
            // カスタマーポイントテーブル更新
            // 現在ポイントを履歴から計算
            $calculateCurrentPoint = $this->app['eccube.plugin.point.repository.point']->getCalculateCurrentPointByCustomerId(
                $order->getCustomer()->getId()
            );
        } else {
            $this->app['eccube.plugin.point.history.service']->saveProvisionalAddPoint($addPoint);
        }

        // 会員ポイント更新
        $this->app['eccube.plugin.point.repository.pointcustomer']->savePoint($calculateCurrentPoint, $order->getCustomer());

        // 履歴情報現在ポイント登録
        $this->app['eccube.plugin.point.history.service']->refreshEntity();
        $this->app['eccube.plugin.point.history.service']->addEntity($order);
        $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());

        if ($add_point_flg) {
            $this->app['eccube.plugin.point.history.service']->saveAfterShoppingCurrentPoint($calculateCurrentPoint);
        }

        // ポイント保存用変数作成 @todo ここのaddの仮ポイントをどうするか
        $point = array();
        $point['current'] = $calculateCurrentPoint;
        $point['use'] = 0 - $usePoint;
        $point['add'] = $addPoint;
        $this->app['eccube.plugin.point.history.service']->refreshEntity();
        $this->app['eccube.plugin.point.history.service']->addEntity($order);
        $this->app['eccube.plugin.point.history.service']->addEntity($order->getCustomer());
        $this->app['eccube.plugin.point.history.service']->saveSnapShot($point);

        // 支払い合計金額更新
        $order->setTotal($amount);
    }
}
