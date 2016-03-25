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
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateType\FrontCart\NonSubtraction;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : カート
 *  - 拡張項目 : 画面表示
 * Class FrontCart
 * @package Plugin\Point\Event\WorkPlace
 */
class FrontCart extends AbstractWorkPlace
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
     * カートページにポイント情報を表示
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        // ポイント情報基本設定を取得
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();

        // ポイント換算率取得
        $point_rate = 0;
        if (!empty($pointInfo)) {
            $point_rate = (integer)$pointInfo->getPlgPointConversionRate();
        }

        // ポイント計算ヘルパーを取得
        $calculator = null;
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // ヘルパーの取得判定
        if (empty($calculator)) {
            return false;
        }

        // カスタマー情報を取得
        $customer = $this->app['security']->getToken()->getUser();

        if(empty($customer)){
            return false;
        }

        // 計算に必要なエンティティを登録
        // カートアイテム(プロダクトクラス)を取得設定
        $parameters = $event->getParameters();

        if(empty($parameters)){
            return false;
        }

        // カートオブジェクトの確認
        if(!isset($parameters['Cart']) || empty($parameters['Cart'])){
            return false;
        }

        // 計算に必要なエンティティを格納
        $calculator->addEntity('Customer', $customer);
        $calculator->addEntity('Cart', $parameters['Cart']);


        // 会員保有ポイントを取得
        $currentPoint = $calculator->getPoint();

        // 会員保有ポイント取得判定
        if (empty($currentPoint)) {
            $currentPoint = 0;
        }

        // 購入商品付与ポイント取得
        $addPoint = $calculator->getAddPointByCart();

        // 購入商品付与ポイント判定
        if (empty($addPoint)) {
            $addPoint = 0;
        }

        // ポイント表示用変数作成
        $point = array();
        $point['current'] = $currentPoint;
        $point['add'] = $addPoint;

        // 使用ポイントボタン付与
        $snippet = $this->app->render(
            'Point/Resource/template/default/Event/Cart/point_box.twig',
            array(
                'point' => $point,
            )
        )->getContent();
        $search = '<div id="cart_item_list"';
        $this->replaceView($event, $snippet, $search);
    }

    /**
     * ポイントデータの保存
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        throw new MethodNotAllowedException();
    }
}
