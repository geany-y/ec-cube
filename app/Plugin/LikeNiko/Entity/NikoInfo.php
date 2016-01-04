<?php
namespace Plugin\LikeNiko\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NikoInfo
 */
class NikoInfo extends \Eccube\Entity\AbstractEntity
{
    const IS_AUTH_ON = 1;
    const IS_AUTH_OFF = 2;

    /**
     * @var integer
     */
    private $id;
    /**
     * @var integer
     */
    private $is_auth_flg;
    /**
     * @var string
     */
    private $form_insert_key;
    /**
     * @var string
     */
    private $replace_block_key;
    /**
     * @var string
     */
    private $target_img_name;
    /**
     * @var integer
     */
    private $target_img_height;
    /**
     * @var integer
     */
    private $target_img_width;
    /**
     * @var string
     */
    private $node_server_address;
    /**
     * Set id
     *
     * @param integer $id
     * @return  NikoInfo
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
     * Set is_auth_flg
     *
     * @param integer $is_auth_flg
     * @return NikoInfo
     */
    public function setIsAuthFlg($is_auth_flg)
    {
        $this->is_auth_flg = $is_auth_flg;
        return $this;
    }
    /**
     * Get is_auth_flg
     *
     * @return integer
     */
    public function getIsAuthFlg()
    {
        return $this->is_auth_flg;
    }
    /**
     * Set form_insert_key
     *
     * @param string $form_insert_key
     * @return NikoInfo
     */
    public function setFormInsertKey($form_insert_key)
    {
        $this->form_insert_key = $form_insert_key;
        return $this;
    }
    /**
     * Get form_insert_key
     *
     * @return string
     */
    public function getFormInsertKey()
    {
        return $this->form_insert_key;
    }
    /**
     * Set replace_block_key
     *
     * @param string $replace_block_key
     * @return NikoInfo
     */
    public function setReplaceBlockKey($replace_block_key)
    {
        $this->replace_block_key = $replace_block_key;
        return $this;
    }
    /**
     * Get replace_block_key
     *
     * @return string
     */
    public function getReplaceBlockKey()
    {
        return $this->replace_block_key;
    }
    /**
     * Set target_img_name
     *
     * @param string $target_img_name
     * @return NikoInfo
     */
    public function setTargetImgName($target_img_name)
    {
        $this->target_img_name = $target_img_name;
        return $this;
    }
    /**
     * Get target_img_name
     *
     * @return string
     */
    public function getTargetImgName()
    {
        return $this->target_img_name;
    }
    /**
     * Set target_img_height
     *
     * @param integer $target_img_height
     * @return NikoInfo
     */
    public function setTargetImgHeight($target_img_height)
    {
        $this->target_img_height = $target_img_height;
        return $this;
    }
    /**
     * Get target_img_height
     *
     * @return integer
     */
    public function getTargetImgHeight()
    {
        return $this->target_img_height;
    }
    /**
     * Set target_img_width
     *
     * @param integer $target_img_width
     * @return NikoInfo
     */
    public function setTargetImgWidth($target_img_width)
    {
        $this->target_img_width = $target_img_width;
        return $this;
    }
    /**
     * Get target_img_width
     *
     * @return integer
     */
    public function getTargetImgWidth()
    {
        return $this->target_img_width;
    }
    /**
     * Set node_server_address
     *
     * @param string $node_server_address
     * @return NikoInfo
     */
    public function setNodeServerAddress($node_server_address)
    {
        $this->node_server_address = $node_server_address;
        return $this;
    }
    /**
     * Get node_server_address
     *
     * @return string
     */
    public function getNodeServerAddress()
    {
        return $this->node_server_address;
    }
}
