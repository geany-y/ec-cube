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


namespace Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateType;

/**
 * 減算あり計算ヘルパー実装クラス
 */
class Subtraction extends NonSubtraction
{
    /**
     * 付与ポイントを返却
     * @return bool|int
     */
    public function getAddPoint(){
        // 付与率の設定確認
        if(is_null($this->productRates)){
            return false;
        }

        // オーダー商品情報がない場合
        if(empty($this->products)){
            return false;
        }

        // 取得ポイント付与率商品ID配列を取得
        if($this->productRates)
        {
            $productKeys = array_keys($this->productRates);
        }

        foreach($this->products as $node){
            $rate = 1;
            $rate = $this->basicRate / 100;
            if($this->productRates) {
                if(in_array($node->getProduct()->getId(), $productKeys)) {
                    // 商品ごとポイント付与率が設定されている場合
                    $rate = $this->productRates[$node->getProduct()->getId()] / 100;
                }
            }
            // 商品毎ポイント付与率が設定されていない場合
            $this->addPoint += (integer)parent::getRoundValue(($node->getProductClass()->getPrice01() * $rate * $node->getQuantity()));
        }

        // 減算値計算
        if(!isset($this->entities['PointUse']) || !isset($this->entities['PointInfo'])) {
            return false;
        }
        $this->usePoint = $this->entities['PointUse']->getPlgUsePoint();
        $conversionRate = $this->entities['PointInfo']->getPlgPointConversionRate();
        $rate = $this->basicRate / 100;
        $usePointAddRate = (integer)parent::getRoundValue(($this->usePoint * $rate) * $conversionRate);

        $this->addPoint = (($this->addPoint - $usePointAddRate) < 0) ? 0 : ($this->addPoint - $usePointAddRate);

        return $this->addPoint;

    }
}
