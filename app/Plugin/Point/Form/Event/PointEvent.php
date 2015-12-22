<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\Point\Form\Event;

use Eccube\ServiceProvider;
use Eccube\Entity\ProductClass;
use Symfony\Component\Form\FormEvent;
use Eccube\Application;
use Eccube\Common\Constant;

/*
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
*/
//use Symfony\Component\EventDispatcher\EventDispatcher;
//use Acme\StoreBundle\Event\StoreSubscriber;


class PointEvent
{
    protected $app;

    public function __construct(){
        $this->app = new \Eccube\Application();
        $this->app->initialize();
    }

    public function onPostSetData(FormEvent $event){
        $form = $event->getForm();
        if ('admin_product' === $form->getName()) {
            //echo '<pre>';
            $productForm = $form->getData();
            //var_dump(get_class_methods($productForm->getProductClasses()));
            //echo '</pre>';
            //exit();
            if (is_null($productForm->getID())) {
                $softDeleteFilter = $this->app['orm.em']->getFilters()->getFilter('soft_delete');
                $softDeleteFilter->setExcludes(array(
                    'Eccube\Entity\ProductClass'
                ));
                $Product = new \Eccube\Entity\Product();
                $ProductClass = new \Eccube\Entity\ProductClass();
                $Disp = $this->app['eccube.repository.master.disp']->find(\Eccube\Entity\Master\Disp::DISPLAY_HIDE);
                $Product
                    ->setDelFlg(Constant::DISABLED)
                    ->addProductClass($ProductClass)
                    ->setStatus($Disp);
                $ProductClass
                    ->setDelFlg(Constant::DISABLED)
                    ->setStockUnlimited(true)
                    ->setProduct($Product);
                $ProductStock = new \Eccube\Entity\ProductStock();
                $ProductClass->setProductStock($ProductStock);
                $ProductStock->setProductClass($ProductClass);
                $ProductPointRate = new \Plugin\Point\Entity\ProductPointRate();
                $ProductPointRate->setProductClassId($ProductClass->getId());
            }

            // 編集処理
            if (!is_null($productForm->getID())) {
                $Product = $this->app['eccube.repository.product']->find($productForm->getID());
                if (!$Product) {
                    throw new NotFoundHttpException();
                }

                // 規格あり商品か
                /*
                $has_class = $Product->hasProductClass();
                if (!$has_class) {
                    // 商品規格/税率/削除/在庫を確認
                    $ProductClasses = $Product->getProductClasses();
                    $ProductClass = $ProductClasses[0];
                    //$BaseInfo = $this->app['eccube.repository.base_info']->getAll();
                    if ($BaseInfo->getOptionProductTaxRule() == Constant::ENABLED && $ProductClass->getTaxRule() && !$ProductClass->getTaxRule()->getDelFlg()) {
                        $ProductClass->setTaxRate($ProductClass->getTaxRule()->getTaxRate());
                    }
                    $ProductStock = $ProductClasses[0]->getProductStock();
                }
                */

                // 商品ポイント付与率設定
                //@todo@ PluginのDIは取得されず
                //$ProductPointRate = new \Plugin\Point\Entity\ProductPointRate();
                $ProductPointRate = $this->app['eccube.plugin.point.repository.pointproduct']->findOneBy(array('product_class_id' => '25'));
               $form->setData($ProductPointRate);
            }
        }
    }
}
