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


namespace Plugin\Point\Resource\lib\PointCalculateHelper\Bridge;

use Plugin\Point\Entity\PointInfo;

/**
 * ポイント計算ヘルパースーパークラス
 *  - ブリッジ / 実装定義クラス
 * Class PointCalculateHelperImplementor
 * @package Plugin\Point\Resource\lib\PointCalculateHelper\Bridge
 */
abstract class PointCalculateHelperImplementor
{
    protected $app;
    protected $originalAmount;
    protected $calculateAmount;

    protected $basicRate;
    protected $productRates;

    protected $point;
    protected $addPoint;
    protected $usePoint;

    protected $actionLocation;
    protected $entities;

    /**
     * PointCalculateHelperImplementor constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
        $this->entities = array();
    }

    /**
     * 計算に必要なエンティティを追加
     * @param Entity $entity
     */
    public function addEntity($entity)
    {
        $entityName = explode(DIRECTORY_SEPARATOR, get_class($entity));
        $this->entities[array_pop($entityName)] = $entity;
    }

    /**
     * 保持エンティティを返却
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * キーをもとに該当エンティティを削除
     * @param string $targetName
     */
    public function removeEntity($targetName)
    {
        if (in_array($targetName, $this->entities[$targetName], true)) {
            unset($this->entities[$targetName]);

            return true;
        }

        return false;
    }

    /**
     * ポイント計算時端数を設定に基づき計算返却
     * @param $value
     * @return bool|float
     */
    protected function getRoundValue($value)
    {
        // ポイント基本設定オブジェクトの有無を確認
        if (!isset($this->entities['PointInfo'])) {
            return false;
        }

        // 計算対象値が引き渡されているか確認
        if (!isset($this->entities['PointInfo'])) {
            return false;
        }

        $calcType = $this->entities['PointInfo']->getPlgCalculationType();

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
     * オーダーエンティティ格納確認と返却
     * @return array|bool
     */
    protected function getOrder()
    {
        // オーダーエンティティの確認
        if (!isset($this->entities['Order'])) {
            return false;
        }

        // オーダー取得
        return $this->entities['Order'];
    }

    /**
     * 商品エンティティ格納確認と返却
     * @return array|bool
     */
    protected function getProducts()
    {
        $order = $this->getOrder();

        // オーダーエンティティの有無確認
        if (empty($order)) {
            return false;
        }

        // 全商品取得
        $products = array();
        foreach ($order->getOrderDetails() as $key => $val) {
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
        $pointUse = 0;
        // 使用ポイントをセッションから取得
        if($this->app['session']->has('usePoint')){
            $pointUse = $this->app['session']->get('usePoint');
        }

        // 現在保有ポイント
        $customer_id = $this->entities['Customer']->getId();
        $point = $this->app['eccube.plugin.point.repository.pointcustomer']->getLastPointById($customer_id);

        // 使用ポイントが保有ポイント内か判定
        if($point < $pointUse)
        {
            return false;
        }


        return true;
    }

    /**
     * ポイント計算付与率を取得設定
     */
    abstract public function attributePointRate();

    /**
     * 仮付与ポイントを返却
     */
    abstract public function getProvisionalAddPoint();

    /**
     * 付与ポイントを返却
     */
    abstract public function getAddPoint();

    /**
     * 保有ポイントを返却
     */
    abstract public function getPoint();

    /**
     * 計算後の販売価格を返却
     */
    abstract public function getTotalAmount();
}
