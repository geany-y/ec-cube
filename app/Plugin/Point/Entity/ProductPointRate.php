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
 * ProductPointRate
 */
class ProductPointRate extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;
    /**
     * @var integer
     */
    private $product_class_id;
    /**
     * @var integer
     */
    private $product_point_rate;
    /**
     * @var datetime
     */
    private $created;
    /**
     * @var datetime
     */
    private $modified;
    /**
     * Set id
     *
     * @param integer $id
     * @return ProductPointRate
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
     * Set prodcut_class_id
     *
     * @param integer $product_class_id
     * @return ProductPointRate
     */
    public function setProductClassId($product_class_id)
    {
        $this->product_class_id = $product_class_id;
        return $this;
    }
    /**
     * Get prodcut_class_id
     *
     * @return product_class_id
     */
    public function getProductClassId()
    {
        return $this->product_class_id;
    }
    /**
     * Set product_point_rate
     *
     * @param decimal $product_point_rate
     * @return ProductPointRate
     */
    public function setProductPointRate($product_point_rate)
    {
        $this->product_point_rate = $product_point_rate;
        return $this;
    }
    /**
     * Get product_point_rate
     *
     * @return integer
     */
    public function getProductPointRate()
    {
        return $this->product_point_rate;
    }
    /**
     * Set created
     *
     * @param datetime $created
     * @return ProductPointRate
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }
    /**
     * Get created
     *
     * @return integer
     */
    public function getCreated()
    {
        return $this->created;
    }
    /**
     * Set modified
     *
     * @param datetime $modified
     * @return ProductPointRate
     */
    public function setModified($modeified)
    {
        $this->modified = $modeified;
        return $this;
    }
    /**
     * Get created
     *
     * @return integer
     */
    public function getModified()
    {
        return $this->modified;
    }
}
