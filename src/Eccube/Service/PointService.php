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

namespace Eccube\Service;

use Doctrine\DBAL\LockMode;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\Customer;
use Eccube\Entity\Delivery;
use Eccube\Entity\Order;
use Eccube\Entity\OrderDetail;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Entity\ShipmentItem;
use Eccube\Entity\Shipping;
use Eccube\Util\Str;

class PointService
{
    /** @var \Eccube\Application */
    public $app;

    /** @var \Eccube\Entity\BaseInfo */
    protected $BaseInfo;

    /** @var  \Eccube\Entity\Order */
    protected $target;

    /** @var  \Doctrine\ORM\EntityManager */
    protected $em;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->BaseInfo = $app['eccube.repository.base_info']->get();
    }

    //---------------------For Interface------------------------------
    /**
     * 受注情報をセット
     *
     * @param \Eccube\Entity\Order
     */
    public function setTarget($Target)
    {
        $this->target = $Target;
    }

    /**
     * ポイントを通過に換算
     *
     * @return \Eccube\Entity\Order
     */
    public function getDiscountValue()
    {
        /*
        echo '<pre>';
        var_dump(get_class_methods($this->target));
        echo '</pre>';
        */
    }

    /**
     * キャンセル処理時の減算ポイントを返却
     *
     * @return \Eccube\Entity\Order
     */
    public function caliculateDiscountAmount()
    {
        //foreach($this->target as $target){
        //foreach($this->target->getOrderDetails() as $target){
        //}
        //}
        echo '<pre>';
        var_dump(get_class_methods($this->target->getOrderDetails()));
        echo '</pre>';
    }
    //---------------------For Interface------------------------------

    //---------------------Follow Concreate------------------------------


    /**
     * キャンセル処理時の減算ポイントを返却
     *
     * @return \Eccube\Entity\Order
     */
    public function getMinusPoint()
    {
    }

    /**
     * 減算設定ポイント付与率の計算
     *
     * @return \Eccube\Entity\Order
     */
    public function getAddPointForCommonRate()
    {
    }

    /**
     * 商品毎ポイント付与率の計算の計算
     *
     * @return \Eccube\Entity\Order
     */
    public function getAddPointForProduct()
    {
    }
    //---------------------Follow Concreate------------------------------
}