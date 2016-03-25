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
 * PointProductRate
 */
class PointProductRate extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $plg_point_product_rate_id;
    /**
     * @var integer
     */
    private $product_id;
    /**
     * @var \Eccube\Entity\Product
     */
    private $Product;
    /**
     * @var integer
     */
    private $plg_point_product_rate;
    /**
     * @var timestamp
     */
    private $create_date;
    /**
     * @var timstamp
     */
    private $update_date;

    /**
     * Get plg_point_product_rate_id
     *
     * @param integer $plg_point_product_rate_id
     *
     * @return PointProductRate
     */
    public function getPlgPointProductRateId()
    {
        return $this->plg_point_product_rate_id;
    }

    /**
     * Set plg_point_product_rate_id
     *
     * @param integer $plg_point_product_rate_id
     * @return PointProductRate
     */
    public function setPlgPointProductRateId($plg_point_product_rate_id)
    {
        $this->plg_point_product_rate_id = $plg_point_product_rate_id;

        return $this;
    }

    /**
     * Get product_id
     *
     * @param integer $product_id
     *
     * @return PointProductRate
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Set product_id
     *
     * @param integer $product_id
     * @return PointProductRate
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;

        return $this;
    }

    /**
     * Get Product
     *
     * @param \Eccube\Entity\Product $product
     *
     * @return PointProductRate
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * Set Product
     *
     * @param \Eccube\Entity\Product $product
     * @return PointProductRate
     */
    public function setProduct($product)
    {
        $this->Product = $product;

        return $this;
    }
    /**
     * Get plg_point_product_rate
     *
     * @param integer $plg_point_product_rate
     *
     * @return PointProductRate
     */
    public function getPlgPointProductRate()
    {
        return $this->plg_point_product_rate;
    }

    /**
     * Set plg_point_product_rate
     *
     * @param integer $plg_point_product_rate
     * @return PointProductRate
     */
    public function setPlgPointProductRate($plg_point_product_rate)
    {
        $this->plg_point_product_rate = $plg_point_product_rate;

        return $this;
    }

    /**
     * Set create_date
     *
     * @param timstamp $create_date
     * @return PointProduct
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * Get created_date
     *
     * @return timstamp $create_date
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param timstamp $update_date
     * @return PointProduct
     */
    public function setUpdateDate($update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return timstamp $update_date
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }
}
