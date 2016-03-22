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
        $calculateHelper = null;
        $calculateHelper = $this->app['eccube.plugin.point.calculate.helper.factory']->createCalculateHelperFunction(
            PointInfo::POINT_CALCULATE_FRONT_CART
        );

        // ヘルパーの取得判定
        if (empty($calculateHelper)) {
            return;
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

        $calculateHelper->addEntityCollection(
            NonSubtraction::ENTITY_COLLECTION_PRODUCT_CLASS,
            $parameters['Cart']->getCartItems()
        );
        $calculateHelper->addEntity($customer);
        $calculateHelper->addEntity($pointInfo);

        // ポイント付与率設定
        $rate_check = $calculateHelper->attributePointRate();

        if(empty($rate_check)){
            return true;
        }

        // ポイント付与率を取得
        $addPoint = $calculateHelper->getAddPoint();

        // ポイント付与率判定
        if (empty($addPoint)) {
            $addPoint = 0;
        }

        // 会員保有ポイントを取得
        $currentPoint = $calculateHelper->getPoint();

        // 会員保有ポイント取得判定
        if (empty($currentPoint)) {
            $currentPoint = 0;
        }

        // 使用ポイントボタン付与
        // twigコードにポイント表示欄を追加
        $snipet = $this->createHtmlDisplayPointFormat();
        $search = '<div id="cart_item_list"';
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        // ポイント表示用変数作成
        $point = array();
        $point['current'] = $currentPoint;
        $point['add'] = $addPoint;

        // twigパラメータにポイント情報を追加
        $parameters = $event->getParameters();
        $parameters['point'] = $point;
        $event->setParameters($parameters);
    }

    /**
     * マイページポイント表示HTML生成
     * @return string
     */
    protected function createHtmlDisplayPointFormat()
    {
        return <<<EOHTML
<p id="cart_item__info" class="message">
現在の保有ポイントは「<strong class="text-primary">&nbsp;{{ point.current }}pt&nbsp;</strong>」です。<br />
商品購入で付与されるポイントは「<strong class="text-primary">&nbsp;{{ point.add }}pt&nbsp;</strong>」です。
</p>
EOHTML;
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
