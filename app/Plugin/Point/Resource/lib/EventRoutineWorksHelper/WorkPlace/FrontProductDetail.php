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
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : 商品詳細
 *  - 拡張項目 : 画面表示・付与ポイント計算
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
        $calculateHelper = null;
        $calculateHelper = $this->app['eccube.plugin.point.calculate.helper.factory']->createCalculateHelperFunction(PointInfo::POINT_CALCULATE_FRONT_COMMON);

        // ヘルパーの取得判定
        if(empty($calculateHelper)){
            return;
        }

        // カスタマー情報を取得
        $customer = $this->app['security']->getToken()->getUser();
        $unCustomer = false;
        if(is_string($customer)) {
            $unCustomer = true;
        }

        // 計算に必要なエンティティを登録
        $calculateHelper->addEntity($product);
        if(!$unCustomer){
            $calculateHelper->addEntity($customer);
        }
        $calculateHelper->addEntity($pointInfo);

        // ポイント付与率設定
        $rate_check = $calculateHelper->attributePointRate();

        //ポイント付与率設定可否判定
        if (is_null($rate_check)) {
            return true;
        }

        // 会員保有ポイントを取得
        $currentPoint = 0;
        if(!$unCustomer) {
            $currentPoint = $calculateHelper->getPoint();

            // 会員保有ポイント取得判定
            if (empty($currentPoint)) {
                $currentPoint = 0;
            }
        }

        // 付与ポイント取得
        $addPoint = $calculateHelper->getAddPoint();

        // 付与ポイント取得判定
        if(empty($addPoint)){
            $addPoint['min'] = 0;
            $addPoint['max'] = 0;
        }

        // 使用ポイントボタン付与
        // twigコードにポイント表示欄を追加
        if($addPoint['min'] == $addPoint['max']) {
            $snipet = $this->createHtmlDisplayPointNotHasClassFormat();
        }else{
            $snipet = $this->createHtmlDisplayPointHasClassFormat();
        }

        $search = '<p id="detail_description_box__item_range_code"';
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        // twigパラメータにポイント情報を追加
        $parameters = $event->getParameters();
        $parameters['point'] = $addPoint;
        $event->setParameters($parameters);
    }

    /**
     * 商品詳細付与率クラスなし表示HTML生成
     * @return string
     */
    protected function createHtmlDisplayPointNotHasClassFormat()
    {
        return <<<EOHTML
<p id="detail_description_box__sale_point" class="text-primary">
    付与ポイント&nbsp;:&nbsp;<span>{{ point.max }}</span>&nbsp;<span class="small">pt</span>
</p>
EOHTML;
    }

    /**
     * 商品詳細付与率クラスあり表示HTML生成
     * @return string
     */
    protected function createHtmlDisplayPointHasClassFormat()
    {
        return <<<EOHTML
<p id="detail_description_box__sale_point" class="text-primary">
    付与ポイント&nbsp;:&nbsp;<span>{{ point.min }}</span>&nbsp;<span class="small">pt</span>&nbsp;～&nbsp;<span>{{ point.max }}</span>&nbsp;<span class="small">pt</span>
</p>
EOHTML;
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
