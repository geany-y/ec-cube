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


namespace Plugin\Point\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PointInfo
 */
class PointInfo extends \Eccube\Entity\AbstractEntity
{
    const ADD_STATUS_FIX = 0;
    const ADD_STATUS_NON_FIX = 1;

    const POINT_ROUND_FLOOR = 0;
    const POINT_ROUND_CEIL = 1;
    const POINT_ROUND_ROUND = 2;

    const POINT_CALCULATE_SUBTRACTION = 0;
    const POINT_CALCULATE_NORMAL = 1;
    const POINT_CALCULATE_FRONT_COMMON = 3;
    const POINT_CALCULATE_FRONT_CART = 4;
    const POINT_CALCULATE_ADMIN_ORDER_NON_SUBTRACTION = 5;
    const POINT_CALCULATE_ADMIN_ORDER_SUBTRACTION = 6;

    /**
     * @var integer
     */
    private $plg_point_info_id;
    /**
     * @var integer
     */
    private $plg_basic_point_rate;
    /**
     * @var integer
     */
    private $plg_point_conversion_rate;
    /**
     * @var smallint
     */
    private $plg_round_type;
    /**
     * @var smallint
     */
    private $plg_calculation_type;
    /**
     * @var smallint
     */
    private $plg_add_point_status;
    /**
     * @var \Plugin\Point\Entity\PointInfoAddStatus
     */
    //private $PointInfoAddStatus;
    /**
     * @var timestamp
     */
    private $create_date;
    /**
     * @var timestamp
     */
    private $update_date;

    /**
     * Set plg_point_info_id
     *
     * @param integer $plg_point_info_id
     * @return PointInfo
     */
    public function setPlgPointInfoId($plg_point_info_id)
    {
        $this->plg_point_info_id = $plg_point_info_id;

        return $this;
    }

    /**
     * Get plg_point_info_id
     *
     * @return integer
     */
    public function getPlgPointInfoId()
    {
        return $this->plg_point_info_id;
    }

    /**
     * Set plg_basic_point_rate
     *
     * @param integer $plg_basic_point_rate
     * @return PointInfo
     */
    public function setPlgBasicPointRate($plg_basic_point_rate)
    {
        $this->plg_basic_point_rate = $plg_basic_point_rate;

        return $this;
    }

    /**
     * Get plg_basic_point_rate
     *
     * @return integer
     */
    public function getPlgBasicPointRate()
    {
        return $this->plg_basic_point_rate;
    }

    /**
     * Set plg_point_conversion_rate
     *
     * @param integer $plg_point_conversion_rate
     * @return PointInfo
     */
    public function setPlgPointConversionRate($plg_point_conversion_rate)
    {
        $this->plg_point_conversion_rate = $plg_point_conversion_rate;

        return $this;
    }

    /**
     * Get plg_point_conversion_rate
     *
     * @return integer
     */
    public function getPlgPointConversionRate()
    {
        return $this->plg_point_conversion_rate;
    }

    /**
     * Set plg_round_type
     *
     * @param smallint $plg_round_type
     * @return PointInfo
     */
    public function setPlgRoundType($plg_round_type)
    {
        $this->plg_round_type = $plg_round_type;

        return $this;
    }

    /**
     * Get plg_round_type
     *
     * @return smallint
     */
    public function getPlgRoundType()
    {
        return $this->plg_round_type;
    }

    /**
     * Set plg_calculation_type
     *
     * @param smallint $plg_calculation_type
     * @return PointInfo
     */
    public function setPlgCalculationType($plg_calculation_type)
    {
        $this->plg_calculation_type = $plg_calculation_type;

        return $this;
    }

    /**
     * Get plg_calculation_type
     *
     * @return smallint
     */
    public function getPlgCalculationType()
    {
        return $this->plg_calculation_type;
    }

    /**
     * Set plg_add_point_status
     *
     * @param smallint $plg_add_point_status
     * @return PointInfo
     */
    public function setPlgAddPointStatus($plg_add_point_status)
    {
        $this->plg_add_point_status = $plg_add_point_status;

        return $this;
    }

    /**
     * Get plg_add_point_status
     *
     * @return smallint
     */
    public function getPlgAddPointStatus()
    {
        return $this->plg_add_point_status;
    }

    /**
     * Set PointInfoAddStatus
     *
     * @param \Plugin\Point\Entity\PointInfoAddStatus $pointInfoAddStatus
     * @return PointInfo
     */
    /*
    public function setPointInfoAddStatus(\Plugin\Point\Entity\PointInfoAddStatus $pointInfoAddStatus)
    {
        $this->PointInfoAddStatus[] = $pointInfoAddStatus;

        return $this;
    }
    */

    /**
     * Remove PointInfo
     *
     * @param \Plugin\Point\Entity\PointInfoAddStatus $pointInfoAddStatus
     */
    /*
    public function removePointInfoAddStatus(\Plugin\Point\Entity\PointInfoAddStatus $pointInfoAddStatus)
    {
        $this->PointInfoAddStatus->removeElement($pointInfoAddStatus);
    }
    */

    /**
     * Get PointInfoAddStatus
     *
     * @return \Plugin\Point\Entity\PointInfoAddStatus
     */
    /*
    public function getPointInfoAddStatus()
    {
        return $this->PointInfoAddStatus;
    }
    */

    /**
     * Set create_date
     *
     * @param timestamp $create_date
     * @return PointInfo
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return timstamp
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param timestamp $update_date
     * @return PointInfo
     */
    public function setUpdateDate($update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return timstamp
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }
}
