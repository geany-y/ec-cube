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


namespace Plugin\Point\Helper\PointCalculateHelper;

use Plugin\Point\Entity\PointInfo;

/**
 * ポイント計算サービスクラス
 * Class PointCalculateHelper
 * @package Plugin\Point\Helper\PointCalculateHelper
 */
class PointCalculateHelper
{
    /** @var \Eccube\Application */
    protected $app;
    /** @var \Plugin\Point\Repository\PointInfoRepository */
    protected $baseInfo;
    /** @var  \Eccube\Entity\ */
    protected $entities;
    protected $products;
    protected $basicRate;
    protected $addPoint;
    protected $productRates;
    protected $usePoint;

    /**
     * PointCalculateHelper constructor.
     * @param \Eccube\Application $app
     */
    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
        // ポイント情報基本設定取得
        $this->baseInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        $this->basicRate = $this->baseInfo->getPlgBasicPointRate();
        $this->entities = array();
        // 使用ポイントをセッションから取得
        $this->usePoint = 0;
        if ($this->app['session']->has('usePoint')) {
            $this->usePoint = $this->app['session']->get('usePoint');
        }
    }

    /**
     * 計算に必要なエンティティを追加
     * @param $name
     * @param \Eccube\Entity $entity
     */
    public function addEntity($name, $entity)
    {
        $this->entities[$name] = $entity;
    }

    /**
     * 保持エンティティを返却
     * @param $name
     * @return array|bool|\Eccube\Entity\
     */
    public function getEntities($name)
    {
        if ($this->hasEntities($name)) {
            return $this->entities[$name];
        }

        return false;
    }

    /**
     * キーをもとに該当エンティティを削除
     * @param $name
     * @return bool
     */
    public function removeEntity($name)
    {
        if ($this->hasEntities($name)) {
            unset($this->entities[$name]);

            return true;
        }

        return false;
    }

    /**
     * 保持エンティティを確認
     * @param $name
     * @return array|bool|\Eccube\Entity\
     */
    public function hasEntities($name)
    {
        if (isset($this->entities[$name])) {
            return true;
        }

        return false;
    }

    /**
     * 利用ポイントの設定
     * @param $usePoint
     * @return bool
     */
    public function setUsePoint($usePoint)
    {
        if (empty($usePoint) && $usePoint != 0) {
            return false;
        }

        $this->usePoint = $usePoint;
    }

    /**
     * ポイント計算時端数を設定に基づき計算返却
     * @param $value
     * @return bool|float
     */
    protected function getRoundValue($value)
    {
        // ポイント基本設定オブジェクトの有無を確認
        if (empty($this->baseInfo)) {
            return false;
        }

        $calcType = $this->baseInfo->getPlgCalculationType();

        // 切り上げ
        if ($calcType == PointInfo::POINT_ROUND_CEIL) {
            return ceil($value);
        }

        // 四捨五入
        if ($calcType == PointInfo::POINT_ROUND_ROUND) {
            return round($value, 0);
        }

        // 切り捨て
        if ($calcType == PointInfo::POINT_ROUND_FLOOR) {
            return round($value, 0);
        }
    }

    /**
     * 受注詳細情報の配列を返却
     * @return array|bool
     */
    protected function getOrderDetail()
    {
        if (!$this->hasEntities('Order')) {
            return false;
        }

        // 全商品取得
        $products = array();
        foreach ($this->entities['Order']->getOrderDetails() as $key => $val) {
            $products[$val->getId()] = $val;
        }

        // 商品がない場合は処理をキャンセル
        if (count($products) < 1) {
            return false;
        }

        return $products;
    }

    /**
     * 利用ポイントが保有ポイント以内に収まっているか計算
     * @return bool
     */
    protected function isInRangeCustomerPoint()
    {
        if (!$this->hasEntities('Customer')) {
            return false;
        }

        // 現在保有ポイント
        $customer_id = $this->entities['Customer']->getId();
        $point = $this->app['eccube.plugin.point.repository.pointcustomer']->getLastPointById($customer_id);

        // 使用ポイントが保有ポイント内か判定
        if ($point < $this->usePoint) {
            return false;
        }

        return true;
    }

    /**
     * 仮付与ポイントを返却
     *  - 会員IDをもとに返却
     * @return bool
     */
    public function getProvisionalAddPoint()
    {
        if (!$this->hasEntities('Customer')) {
            return false;
        }

        $customer_id = $this->entities['Customer']->getId();
        $provisionalPoint = $this->app['eccube.plugin.point.repository.point']->getAllProvisionalAddPoint($customer_id);

        if (!empty($provisionalPoint)) {
            return $provisionalPoint;
        }

        return false;
    }

    /**
     * 仮付与ポイントを返却
     *  - オーダー情報をもとに返却
     * @return bool
     */
    public function getProvisionalAddPointByOrder()
    {
        if (!$this->hasEntities('Customer')) {
            return false;
        }
        if (!$this->hasEntities('Order')) {
            return false;
        }

        $order = $this->entities['Order'];
        $provisionalPoint = $this->app['eccube.plugin.point.repository.point']->getProvisionalAddPointByOrder($order);


        if (!empty($provisionalPoint)) {
            return $provisionalPoint;
        }

        return false;
    }

    /**
     * カート情報をもとに付与ポイントを返却
     * @return bool|int
     */
    public function getAddPointByCart()
    {
        // カートエンティティチェック
        if (empty($this->entities['Cart'])) {
            return false;
        }

        // 商品毎のポイント付与率を取得
        $productClasses = array();
        $cartObjects = array();
        foreach ($this->entities['Cart']->getCartItems() as $cart) {
            $productClasses[] = $cart->getObject();     // 商品毎ポイント付与率取得用
            $cartObjects[] = $cart;                     // 購入数を判定するためのカートオブジェジェクト
        }

        // 商品毎のポイント付与率取得
        $productRates = $this->app['eccube.plugin.point.repository.pointproductrate']->getPointProductRateByEntity(
            $productClasses
        );

        // 付与率の設定がされていない場合
        if (count($productRates) < 1) {
            $productRates = false;
        }

        // 商品毎のポイント付与率セット
        $this->productRates = $productRates;

        // 取得ポイント付与率商品ID配列を取得
        if ($this->productRates) {
            $productKeys = array_keys($this->productRates);
        }

        // 商品詳細ごとの購入金額にレートをかける
        // レート計算後個数をかける
        foreach ($cartObjects as $node) {
            $rate = 1;
            // 商品毎ポイント付与率が設定されていない場合
            $rate = $this->basicRate / 100;
            if ($this->productRates) {
                if (in_array($node->getObject()->getProduct()->getId(), $productKeys)) {
                    // 商品ごとポイント付与率が設定されている場合
                    $rate = $this->productRates[$node->getObject()->getProduct()->getId()] / 100;
                }
            }
            $this->addPoint += (integer)$this->getRoundValue(
                ($node->getObject()->getPrice01() * $rate * $node->getQuantity())
            );
        }

        // 減算処理の場合減算値を返却
        if ($this->isSubtraction()) {
            return $this->getSubtractionCalculate();
        }

        return $this->addPoint;
    }

    /**
     * 受注情報をもとに付与ポイントを返却
     * @return bool|int
     */
    public function getAddPointByOrder()
    {
        $this->addPoint = 0;
        if (!$this->hasEntities('Order')) {
            return false;
        }

        // 商品詳細情報ををオーダーから取得
        $this->products = $this->getOrderDetail();

        // 商品ごとのポイント付与率を取得
        $productRates = $this->app['eccube.plugin.point.repository.pointproductrate']->getPointProductRateByEntity(
            $this->products
        );

        // 付与率の設定がされていない場合
        if (count($productRates) < 1) {
            $productRates = false;
        }

        // 商品ごとのポイント付与率セット
        $this->productRates = $productRates;

        // 取得ポイント付与率商品ID配列を取得
        if ($this->productRates) {
            $productKeys = array_keys($this->productRates);
        }

        // 商品詳細ごとの購入金額にレートをかける
        // レート計算後個数をかける
        foreach ($this->products as $node) {
            $rate = 1;
            // 商品毎ポイント付与率が設定されていない場合
            $rate = $this->basicRate / 100;
            if ($this->productRates) {
                if (in_array($node->getProduct()->getId(), $productKeys)) {
                    // 商品ごとポイント付与率が設定されている場合
                    $rate = $this->productRates[$node->getProduct()->getId()] / 100;
                }
            }
            $this->addPoint += (integer)$this->getRoundValue(
                ($node->getProductClass()->getPrice01() * $rate * $node->getQuantity())
            );
        }

        // 減算処理の場合減算値を返却
        if ($this->isSubtraction()) {
            return $this->getSubtractionCalculate();
        }

        return $this->addPoint;
    }

    /**
     * 商品情報から付与ポイントを返却
     * @return array|bool
     */
    public function getAddPointByProduct()
    {
        if (!$this->hasEntities('Product')) {
            return false;
        }

        // 商品毎のレートが設定されているか確認
        $pointRate = $this->app['eccube.plugin.point.repository.pointproductrate']->getLastPointProductRateById(
            $this->entities['Product']->getId()
        );
        // サイト全体でのポイント設定
        $basicPointRate = $this->baseInfo->getPlgBasicPointRate();

        // 基本付与率の設定判定
        if (empty($basicPointRate)) {
            return false;
        }

        // 商品毎の付与率あればそちらを優先
        // なければサイト設定ポイントを利用
        $calculateRate = $basicPointRate;
        if (!empty($pointRate)) {
            $calculateRate = $pointRate;
        }

        // 金額の取得
        $min_price = $this->entities['Product']->getPrice01Min();
        $max_price = $this->entities['Product']->getPrice01Max();

        // 返却値生成
        $rate = array();
        $rate['min'] = (integer)$this->getRoundValue($min_price * ((integer)$calculateRate / 100));
        $rate['max'] = (integer)$this->getRoundValue($max_price * ((integer)$calculateRate / 100));

        return $rate;
    }

    /**
     * ポイント機能基本情報から計算方法を取得し判定
     * @return bool
     */
    protected function isSubtraction()
    {
        // 基本情報が設定されているか確認
        if (!empty($this->baseInfo)) {
            return false;
        }

        // 計算方法の判定
        if ($this->baseInfo == PointInfo::POINT_CALCULATE_ADMIN_ORDER_SUBTRACTION) {
            return true;
        }

        return false;
    }

    /**
     * 利用ポイント減算処理
     * @return bool|int
     */
    protected function getSubtractionCalculate()
    {
        // 基本情報が設定されているか確認
        if (!empty($this->baseInfo->getplgPointCalculateType)) {
            return false;
        }

        // 減算値計算
        if (!isset($this->usePoint) || empty($this->usePoint)) {
            return false;
        }

        $conversionRate = $this->baseInfo->getPlgPointConversionRate();
        $rate = $this->basicRate / 100;
        $usePointAddRate = (integer)$this->getRoundValue(($this->usePoint * $rate) * $conversionRate);

        $this->addPoint = (($this->addPoint - $usePointAddRate) < 0) ? 0 : ($this->addPoint - $usePointAddRate);

        return $this->addPoint;
    }

    /**
     * 保有ポイントを返却
     * @return bool
     */
    public function getPoint()
    {
        if (!$this->hasEntities('Customer')) {
            return false;
        }

        $customer_id = $this->entities['Customer']->getId();
        $point = $this->app['eccube.plugin.point.repository.pointcustomer']->getLastPointById($customer_id);

        return $point;
    }

    /**
     * 計算後の販売価格を返却
     */
    public function getTotalAmount()
    {
        if (!$this->hasEntities('Order')) {
            return false;
        }

        if (count($this->products) < 1) {
            return false;
        }

        $total = 0;
        foreach ($this->products as $product) {
            $total += $product->getPrice() * $product->getQuantity();
        }

        $nonTaxPrice = $this->entities['Order']->getSubTotal() - $this->entities['Order']->getTax();

        // 使用ポイントが保有ポイント内か確認
        if (!$this->isInRangeCustomerPoint()) {
            return false;
        }

        // 税率再設定
        $pointMinusPrice = $nonTaxPrice - $this->usePoint;
        $details = $this->entities['Order']->getOrderDetails();
        $taxRate = $details[0]->getTaxRate();
        $taxRule = $details[0]->getTaxRule();
        $newTax = $this->app['eccube.service.tax_rule']->calcTax($pointMinusPrice, $taxRate, $taxRule);
        $this->entities['Order']->setTax($newTax);

        // ポイント換算値をもとに計算返却
        $conversionRate = $this->baseInfo->getPlgPointConversionRate();

        return (integer)$this->getRoundValue($this->entities['Order']->getTotal() - $this->usePoint * $conversionRate);
    }
}
