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

use Doctrine\ORM\Mapping as ORM;

/**
 * PointCustomer
 */
class PointCustomer extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $plg_point_customer_id;
    /**
     * @var integer
     */
    private $plg_point_current;
    /**
     * @var integer
     */
    private $customer_id;
    /**
     * @var \Eccube\Entity\Customer
     */
    private $Customer;
    /**
     * @var date
     */
    private $create_date;
    /**
     * @var date
     */
    private $update_date;

    /**
     * Set plg_point_customer_id
     *
     * @param integer $plg_point_customer_id
     * @return PointCustomer
     */
    public function setPlgPointCustomerId($plg_point_customer_id)
    {
        $this->plg_point_customer_id = $plg_point_customer_id;

        return $this;
    }

    /**
     * Get plg_point_customer_id
     *
     * @return integer
     */
    public function getPlgPointCustomerId()
    {
        return $this->plg_point_customer_id;
    }

    /**
     * Set plg_point_current
     *
     * @param integer $plg_point_current
     * @return PointCustomer
     */
    public function setPlgPointCurrent($plg_point_current)
    {
        $this->plg_point_current = $plg_point_current;

        return $this;
    }

    /**
     * Get plg_point_current
     *
     * @return integer plg_point_current
     */
    public function getPlgPointCurrent()
    {
        return $this->plg_point_current;
    }

    /**
     * Set customer_id
     *
     * @param integer $customer_id
     * @return PointCustomer
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * Get customer_id
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Set Customer
     *
     * @param \Eccube\Entity\Customer $Customer
     * @return PointCustomer
     */
    public function setCustomer($Customer)
    {
        $this->Customer = $Customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \Eccube\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * Set create_date
     *
     * @param date $create_date
     * @return PointCustomer
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return date $create_date
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param date $update_date
     * @return PointCustomer
     */
    public function setUpdateDate($update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return date $update_date
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }
}
