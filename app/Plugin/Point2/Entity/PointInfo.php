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


namespace Plugin\Point2\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PointInfo
 */
class PointInfo extends \Eccube\Entity\AbstractEntity
{
    const POINT_OFF = 0;
    const POINT_ON = 1;
    const POINT_SUBTRACT_OFF = 0;
    const POINT_SUBTRACT_ON = 1;
    const POINT_MATH_FLOOR = 0;
    const POINT_ROUND_CEIL = 1;
    const POINT_ROUND_ROUND = 2;

    /**
     * @var integer
     */
    private $id;
    /**
     * @var integer
     */
    private $basic_point_rate;
    /**
     * @var \Eccube\Entity\Master\AddpointStatus
     */
    private $OrderStatus;
    /**
     * @var integer
     */
    private $point_caliculate_type;
    /**
     * @var integer
     */
    private $point_conversion_rate;
    /**
     * Set id
     *
     * @param integer $id
     * @return BaseInfo
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set basic_point_rate
     *
     * @param integer $basic_point_rate
     * @return BaseInfo
     */
    public function setBasicPointRate($basic_point_rate)
    {
        $this->basic_point_rate = $basic_point_rate;
        return $this;
    }
    /**
     * Get basic_point_rate
     *
     * @return integer
     */
    public function getBasicPointRate()
    {
        return $this->basic_point_rate;
    }
    /**
     * Set OrderStatus
     *
     * @param  \Eccube\Entity\Master\OrderStatus $orderStatus
     * @return BaseInfo
     */
    public function setOrderStatus(\Eccube\Entity\Master\OrderStatus $OrderStatus = null)
    {
        $this->OrderStatus = $OrderStatus;
        return $this;
    }
    /**
     * Get OrderStatus
     *
     * @return \Eccube\Entity\Master\OrderStatus
     */
    public function getOrderStatus()
    {
        return $this->OrderStatus;
    }
    /**
     * Set point_caliculate_type
     *
     * @param integer $point_caliculate_type
     * @return BaseInfo
     */
    public function setPointCaliculateType($point_caliculate_type)
    {
        $this->point_caliculate_type = $point_caliculate_type;
        return $this;
    }
    /**
     * Get point_caliculate_type
     *
     * @return integer
     */
    public function getPointCaliculateType()
    {
        return $this->point_caliculate_type;
    }
    /**
     * Set point_conversion_rate
     *
     * @param integer $
     * @return BaseInfo
     */
    public function setPointConversionRate($point_conversion_rate)
    {
        $this->point_conversion_rate = $point_conversion_rate;
        return $this;
    }
    /**
     * Get point_conversion_rate
     *
     * @return integer
     */
    public function getPointConversionRate()
    {
        return $this->point_conversion_rate;
    }
}
