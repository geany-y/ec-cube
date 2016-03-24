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


namespace Plugin\Point\Helper\EventRoutineWorksHelper;

use Plugin\Point\Event\WorkPlace\ServiceMail;
use Plugin\Point\PointEventHandler;
use Plugin\Point\Event\WorkPlace\AdminCustomer;
use Plugin\Point\Event\WorkPlace\AdminOrder;
use Plugin\Point\Event\WorkPlace\AdminProduct;
use Plugin\Point\Event\WorkPlace\FrontCart;
use Plugin\Point\Event\WorkPlace\FrontMyPage;
use Plugin\Point\Event\WorkPlace\FrontProductDetail;
use Plugin\Point\Event\WorkPlace\FrontShopping;
use Plugin\Point\Event\WorkPlace\FrontShoppingConfirm;

/**
 * フックポイント定型処理ヘルパーのファクトリー
 * Class EventRoutineWorksHelperFactory
 * @package Plugin\Point\Helper\EventRoutineWorksHelper
 */
class EventRoutineWorksHelperFactory
{
    /** @var \Eccube\Application */
    protected $app;

    /**
     * EventRoutineWorksHelperFactory constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * キーを元に、該当フックポイント定型処理ヘルパーインスタンスを返却
     * @param $key
     * @return EventRoutineWorksHelperFactory
     */
    public function createEventRoutineWorksHelper($key)
    {
        switch ($key) {
            case PointEventHandler::HELPER_ADMIN_PRODUCT :
                return new EventRoutineWorksHelper(new AdminProduct());
                break;
            case PointEventHandler::HELPER_ADMIN_CUSTOMER :
                return new EventRoutineWorksHelper(new AdminCustomer());
                break;
            case PointEventHandler::HELPER_ADMIN_ORDER :
                return new EventRoutineWorksHelper(new AdminOrder());
                break;
            case PointEventHandler::HELPER_FRONT_SHOPPING :
                return new EventRoutineWorksHelper(new FrontShopping());
                break;
            case PointEventHandler::HELPER_FRONT_SHOPPING_CONFIRM :
                return new EventRoutineWorksHelper(new FrontShoppingConfirm());
                break;
            case PointEventHandler::HELPER_FRONT_MYPAGE :
                return new EventRoutineWorksHelper(new FrontMyPage());
                break;
            case PointEventHandler::HELPER_FRONT_PRODUCT_DETAIL :
                return new EventRoutineWorksHelper(new FrontProductDetail());
                break;
            case PointEventHandler::HELPER_FRONT_CART :
                return new EventRoutineWorksHelper(new FrontCart());
                break;
            case PointEventHandler::HELPER_SERVICE_MAIL :
                return new EventRoutineWorksHelper(new ServiceMail());
                break;
            default :
                break;
        }
    }
}
