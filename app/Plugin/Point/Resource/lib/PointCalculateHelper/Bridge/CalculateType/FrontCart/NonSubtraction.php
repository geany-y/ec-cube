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


namespace Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateType\FrontCart;

/**
 * フロント画面カート専用計算ヘルパー実装クラス
 *  - 減算なし
 * Class NonSubtractionCalculateServiceImplementor
 * @package Plugin\Point\Service\PointCalculateHelper\Calculate
 */
class NonSubtraction extends \Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateType\NonSubtraction
{
    const ENTITY_COLLECTION_PRODUCT_CLASS = 'ProductClass';

    /**
     * 計算に必要なエンティティ配列を追加
     * @param $name
     * @param $entityCollection
     */
    public function addEntityCollection($name, $entityCollection)
    {
        $this->entities[$name] = $entityCollection;
    }

    /**
     * ポイント計算付与率を取得設定
     */
    public function attributePointRate()
    {
        // ポイント基本設定エンティティの確認
        if (!isset($this->entities['PointInfo'])) {
            return null;
        }

        // ポイント基本設定情報を取得
        $basicRate = $this->entities['PointInfo']->getPlgBasicPointRate();

        // 基本付与率の設定がされていない場合
        if (empty($basicRate)) {
            return null;
        }

        // 基本付与率設定
        $this->basicRate = $basicRate;

        // 商品毎のポイント付与率を取得
        $this->products = array();
        foreach ($this->entities[self::ENTITY_COLLECTION_PRODUCT_CLASS] as $productClass) {
            $this->products[] = $productClass->getObject();
        }
        $productRates = $this->app['eccube.plugin.point.repository.pointproductrate']->getPointProductRateByEntity(
            $this->products
        );

        // 付与率の設定がされていない場合
        if (count($productRates) < 1) {
            $productRates = false;
        }

        $this->productRates = $productRates;

        return true;
    }

    /**
     * 付与ポイントを返却
     */
    public function getAddPoint()
    {
        // オーダー商品情報がない場合
        if (empty($this->products)) {
            return false;
        }

        // 取得ポイント付与率商品ID配列を取得
        if ($this->productRates) {
            $productKeys = array_keys($this->productRates);
        }

        foreach ($this->entities[self::ENTITY_COLLECTION_PRODUCT_CLASS] as $cartItem) {
            $rate = 1;
            // 商品毎ポイント付与率が設定されていない場合
            $rate = $this->basicRate / 100;
            if ($this->productRates) {
                if (in_array($cartItem->getObject()->getProduct()->getId(), $productKeys)) {
                    // 商品ごとポイント付与率が設定されている場合
                    $rate = $this->productRates[$cartItem->getObject()->getProduct()->getId()] / 100;
                }
            }
            $this->addPoint += $cartItem->getObject()->getPrice01() * $rate * $cartItem->getQuantity();
        }

        return (integer)parent::getRoundValue($this->addPoint);
    }

    /**
     * 計算後の販売価格を返却
     */
    /*
    public function getTotalAmount()
    {
        $total = 0;
        foreach($products as $product){
            $total += $product->getPrice() * $product->getQuantity();
        }

        // 使用ポイントが保有ポイント内か確認
        if(!$this->isInRangeCustomerPoint()){
            return false;
        }

        // 税率再設定
        $details = $order->getOrderDetails();
        $taxRate = $details[0]->getTaxRate();
        $taxRule = $details[0]->getTaxRule();
        $newTax = $this->app['eccube.service.tax_rule']->calcTax($pointMinusPrice, $taxRate, $taxRule);
        $order->setTax($newTax);

        // ポイント換算値をもとに計算返却
        $conversionRate = $this->entities['PointInfo']->getPlgPointConversionRate();
        return (integer)parent::getRoundValue($total * $conversionRate);
    }
    */
}
