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


namespace Plugin\Point\Resource\lib\PointCalculateHelper;

use Doctrine\ORM\Mapping\Entity;
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\PointCalculateHelperImplementor;

/**
 * ポイント計算サービススーパークラス
 *  - ブリッジ / 機能定義クラス
 */
class PointCalculateHelper
{
    protected $calculator;

    /**
     * PointCalculateHelper constructor.
     * @param PointCalculateHelperImplementor $calculator
     */
    public function __construct(PointCalculateHelperImplementor $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * 計算に必要なエンティティを追加
     * @param Entity $entity
     */
    public function addEntity($entity)
    {
        $this->calculator->addEntity($entity);
    }

    /**
     * 保持エンティティを返却
     * @return array
     */
    public function getEntities()
    {
        return $this->calculator->getEntities();
    }

    /**
     * キーをもとに該当エンティティを削除
     * @param string $targetName
     */
    public function removeEntity($targetName)
    {
        $this->calculator->removeEntity($targetName);
    }

    /**
     * ポイント計算付与率を返却
     */
    public function attributePointRate()
    {
        return $this->calculator->attributePointRate();
    }

    /**
     * ユーザー保有仮ポイントを返却
     */
    public function getProvisionalAddPoint()
    {
        return $this->calculator->getProvisionalAddPoint();
    }

    /**
     * 付与ポイントを返却
     */
    public function getAddPoint()
    {
        return $this->calculator->getAddPoint();
    }

    /**
     * 保有ポイントを返却
     */
    public function getPoint()
    {
        return $this->calculator->getPoint();
    }

    /**
     * 計算後の販売価格を返却
     */
    public function getTotalAmount()
    {
        return $this->calculator->getTotalAmount();
    }
}
