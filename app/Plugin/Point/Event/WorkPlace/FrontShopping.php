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
     */
    public function createTwig(TemplateEvent $event)
    {
        $args = $event->getParameters();
        $order = $args['Order'];

        // オーダーエンティティの確認
        if(empty($order)){
            return false;
        }

        // 利用ポイントの確認
        $pointUse = new PointUse();
        $usePoint = 0;
        if ($this->app['session']->has('usePoint')) {
            $usePoint = $this->app['session']->get('usePoint');
            $pointUse->setPlgUsePoint($usePoint);
        }

        // 計算判定取得
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 計算ヘルパー取得判定
        if (is_null($calculator)) {
            return true;
        }

        // 計算に必要なエンティティを登録
        $calculator->addEntity('Order', $order);
        $calculator->addEntity('Customer', $order->getCustomer());

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

        // ポイント使用合計金額取得・設定
        $amount = $calculator->getTotalAmount();

        // 合計金額をセット
        $order->setTotal($amount);

        // ポイント表示用変数作成
        $point = array();
        $point['current'] = $currentPoint;
        // エラー判定
        // false が返却された際は、利用ポイント値が保有ポイント値を超えている
        if ($amount == false) {
            $point['use_error'] = 'front.point.display.usepointe.error';
        } else {
            $point['use'] = 0 - $usePoint;
        }
        $point['add'] = $addPoint;

        // Twigデータ内IDをキーに表示項目を追加
        // ポイント情報表示
        // false が返却された際は、利用ポイント値が保有ポイント値を超えている
        if ($amount == false) {
            //$snippet = $this->createHtmlDisplayPointUseOverErrorFormat();
            $snippet = $this->app->render(
                'Point/Resource/template/default/Event/ShoppingConfirm/point_summary_error.twig',
                array(
                    'point' => $point,
                )
            )->getContent();
        } else {
            //$snippet = $this->createHtmlDisplayPointFormat();
            $snippet = $this->app->render(
                'Point/Resource/template/default/Event/ShoppingConfirm/point_summary.twig',
                array(
                    'point' => $point,
                )
            )->getContent();
        }
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
        $search = '<a id="confirm_box__quantity_edit_button"';
        $this->replaceView($event, $snippet, $search);
    }

    /**
     * 本クラスでは処理なし
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        throw new MethodNotAllowedException();
    }
}
