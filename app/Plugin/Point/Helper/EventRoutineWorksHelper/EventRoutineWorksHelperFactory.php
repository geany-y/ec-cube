<?php

namespace Plugin\Point\Helper\EventRoutineWorksHelper;

use Plugin\Point\Event\WorkPlace\FrontDelivery;
use Plugin\Point\Event\WorkPlace\FrontHistory;
use Plugin\Point\Event\WorkPlace\FrontPayment;
use Plugin\Point\Event\WorkPlace\FrontShipping;
use Plugin\Point\Event\WorkPlace\FrontShoppingComplete;
use Plugin\Point\Event\WorkPlace\ServiceMail;
use Plugin\Point\PointEvent;
use Plugin\Point\Event\WorkPlace\AdminCustomer;
use Plugin\Point\Event\WorkPlace\AdminOrder;
use Plugin\Point\Event\WorkPlace\AdminProduct;
use Plugin\Point\Event\WorkPlace\FrontCart;
use Plugin\Point\Event\WorkPlace\FrontMyPage;
use Plugin\Point\Event\WorkPlace\FrontProductDetail;
use Plugin\Point\Event\WorkPlace\FrontShopping;

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
     * @return EventRoutineWorksHelper
     */
    public function createEventRoutineWorksHelper($key)
    {
        switch ($key) {
            case PointEvent::HELPER_ADMIN_PRODUCT :
                return new EventRoutineWorksHelper(new AdminProduct());
                break;
            case PointEvent::HELPER_ADMIN_CUSTOMER :
                return new EventRoutineWorksHelper(new AdminCustomer());
                break;
            case PointEvent::HELPER_ADMIN_ORDER :
                return new EventRoutineWorksHelper(new AdminOrder());
                break;
            case PointEvent::HELPER_FRONT_SHOPPING :
                return new EventRoutineWorksHelper(new FrontShopping());
                break;
            case PointEvent::HELPER_FRONT_DELIVERY :
                return new EventRoutineWorksHelper(new FrontDelivery());
                break;
            case PointEvent::HELPER_FRONT_PAYMENT :
                return new EventRoutineWorksHelper(new FrontPayment());
                break;
            case PointEvent::HELPER_FRONT_SHIPPING :
                return new EventRoutineWorksHelper(new FrontShipping());
                break;
            case PointEvent::HELPER_FRONT_SHOPPING_COMPLETE :
                return new EventRoutineWorksHelper(new FrontShoppingComplete());
                break;
            case PointEvent::HELPER_FRONT_MYPAGE :
                return new EventRoutineWorksHelper(new FrontMyPage());
                break;
            case PointEvent::HELPER_FRONT_PRODUCT_DETAIL :
                return new EventRoutineWorksHelper(new FrontProductDetail());
                break;
            case PointEvent::HELPER_FRONT_CART :
                return new EventRoutineWorksHelper(new FrontCart());
                break;
            case PointEvent::HELPER_SERVICE_MAIL :
                return new EventRoutineWorksHelper(new ServiceMail());
                break;
            case PointEvent::HELPER_FRONT_HISTORY :
                return new EventRoutineWorksHelper(new FrontHistory());
                break;
            default :
                throw new \Prophecy\Exception\Doubler\ClassNotFoundException();
                break;
        }
    }
}
