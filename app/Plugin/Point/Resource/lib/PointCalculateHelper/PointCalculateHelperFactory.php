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

use Plugin\Point\Entity\PointInfo;
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateLocation\AdminOrder;
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateLocation\FrontCommon;
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateLocation\Type\AdminOrderNonSubtraction;
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateLocation\Type\AdminOrderSubtraction;
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateType\NonSubtraction;
use Plugin\Point\Resource\lib\PointCalculateHelper\Bridge\CalculateType\Subtraction;

/**
 * 計算サービスを生成クラス
 * Class PointCalculateHelperFactory
 * @package Plugin\Point\Resource\lib\PointCalculateHelper
 */
class PointCalculateHelperFactory
{
    /**
     * キーに応じて該当計算ヘルパーを返却
     * @param $key
     * @return PointCalculateHelper
     */
    public function createCalculateHelperFunction($key){
        switch($key){
            case PointInfo::POINT_CALCULATE_NORMAL :
                return new PointCalculateHelper(new NonSubtraction());
                break;
            case PointInfo::POINT_CALCULATE_SUBTRACTION :
                return new PointCalculateHelper(new Subtraction());
                break;
            case PointInfo::POINT_CALCULATE_FRONT_COMMON :
                return new PointCalculateHelper(new FrontCommon());
                break;
            case PointInfo::POINT_CALCULATE_FRONT_CART :
                return new PointCalculateHelper(new FrontCart());
                break;
            default :
                break;
        }
    }
}
