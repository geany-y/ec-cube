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


namespace Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateLocation;

use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\PointCalculateHelperImplementor;

/**
 * フロント画面汎用ポイント計算ヘルパー実装クラス
 * Class NonSubtractionCalculateServiceImplementor
 * @package Plugin\Point\Service\PointCalculateHelper\Calculate
 */
class FrontCommon extends PointCalculateHelperImplementor
{
    /**
     * ポイント計算付与率を取得設定
     */
    public function attributePointRate()
    {
        // ポイント基本設定エンティティの確認
        if(!isset($this->entities['PointInfo'])){
            return null;
        }

        // ポイント基本付与率
        $basicRate = $this->entities['PointInfo']->getPlgBasicPointRate();

        // 基本付与率の設定がされていない場合
        if(empty($basicRate))
        {
            return null;
        }

        // 基本付与率設定
        $this->basicRate = $basicRate;

        return true;
    }

    /**
     * 仮付与ポイントを返却
     * @return bool|int
     */
    public function getProvisionalAddPoint()
    {
        $customer_id = $this->entities['Customer']->getId();
        $provisionalPoint = $this->app['eccube.plugin.point.repository.point']->getAllProvisionalAddPoint($customer_id);

        if(!empty($provisionalPoint)){
            return $provisionalPoint;
        }
        return false;
    }

    /**
     * 付与ポイントを返却
     */
    public function getAddPoint()
    {
        if(!isset($this->entities['Product'])){
            return null;
        }

        // 商品毎のレートが設定されているか確認
        $pointRate = $this->app['eccube.plugin.point.repository.pointproductrate']->getLastPointProductRateById($this->entities['Product']->getId());
        $basicPointRate = $this->entities['PointInfo']->getPlgBasicPointRate();

        // 基本付与率の設定判定
        if(empty($basicPointRate)){
            return false;
        }

        $calculateRate = $basicPointRate;
        if(!empty($pointRate)){
            $calculateRate = $pointRate;
        }


        // 金額の取得
        $min_price = $this->entities['Product']->getPrice01Min();
        $max_price = $this->entities['Product']->getPrice01Max();

        // 返却値生成
        $rate = array();
        $rate['min'] = (integer)parent::getRoundValue($min_price * ((integer)$calculateRate / 100));
        $rate['max'] = (integer)parent::getRoundValue($max_price * ((integer)$calculateRate / 100));

        return $rate;
    }

    /**
     * 保有ポイントを返却
     */
    public function getPoint()
    {
        $customer_id = $this->entities['Customer']->getId();
        $point = $this->app['eccube.plugin.point.repository.pointcustomer']->getLastPointById($customer_id);

        return $point;
    }

    /**
     * 計算後の販売価格を返却
     */
    public function getTotalAmount()
    {
    }
}
