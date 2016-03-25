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
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : 商品詳細
 *  - 拡張項目 : 画面表示・付与ポイント計算
 * Class FrontProductDetail
 * @package Plugin\Point\Event\WorkPlace
 */
class FrontProductDetail extends AbstractWorkPlace
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
     * 商品詳細画面に付与ポイント表示
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        // 商品エンティティを取得
        $parameters = $event->getParameters();
        $product = $parameters['Product'];

        if(empty($product)){
            return false;
        }

        // ポイント基本情報設定エンティティを取得
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();

        $point_rate = 0;
        if(!empty($pointInfo))
        {
            $point_rate = (integer)$pointInfo->getPlgPointConversionRate();
        }

        // ポイント計算ヘルパーを取得
        $calculator = null;
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // ヘルパーの取得判定
        if(empty($calculator)){
            return false;
        }

        // カスタマー情報を取得
        $customer = $this->app['security']->getToken()->getUser();
        $unCustomer = false;
        if(is_string($customer)) {
            $unCustomer = true;
        }

        // 計算に必要なエンティティを登録
        $calculator->addEntity('Product', $product);
        if(!$unCustomer){
            $calculator->addEntity('Customer', $customer);
        }

        // 会員保有ポイントを取得
        $currentPoint = 0;
        if(!$unCustomer) {
            $currentPoint = $calculator->getPoint();

            // 会員保有ポイント取得判定
            if (empty($currentPoint)) {
                $currentPoint = 0;
            }
        }

        // 付与ポイント取得
        $point = $calculator->getAddPointByProduct();

        // 付与ポイント取得判定
        if(empty($point)){
            $point['min'] = 0;
            $point['max'] = 0;
        }

        // 使用ポイントボタン付与
        // twigコードにポイント表示欄を追加
        if($point['min'] == $point['max']) {
            //$snippet = $this->createHtmlDisplayPointNotHasClassFormat();
            $snippet = $this->app->render(
                'Point/Resource/template/default/Event/ProductDetail/detail_point.twig',
                array(
                    'point' => $point,
                )
            )->getContent();
        }else{
            //$snippet = $this->createHtmlDisplayPointHasClassFormat();
            $snippet = $this->app->render(
                'Point/Resource/template/default/Event/ProductDetail/detail_point_error.twig',
                array(
                    'point' => $point,
                )
            )->getContent();
        }

        $search = '<p id="detail_description_box__item_range_code"';
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
