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


namespace Plugin\Point\Event\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : 商品登録( 編集 )
 *  - 拡張項目 : 商品毎ポイント付与率( 編集 )
 * Class AdminProduct
 * @package Plugin\Point\Event\WorkPlace
 */
class  AdminProduct extends AbstractWorkPlace
{
    /**
     * 商品フォームポイント付与率項目追加
     * @param FormBuilder $builder
     * @param Request $request
     */
    public function createForm(FormBuilder $builder, Request $request)
    {
        $productId = $builder->getForm()->getData()->getId();

        // 登録済み情報取得処理
        $lastPointProduct = null;
        if(!is_null($productId)) {
            $lastPointProduct = $this->app['eccube.plugin.point.repository.pointproductrate']->getLastPointProductRateById($productId);
        }

        $data = is_null($lastPointProduct) ? '' : $lastPointProduct;

        // ポイント付与率項目拡張
        $builder
            ->add(
                'plg_point_product_rate',
                'text',
                array(
                    'label' => 'ポイント付与率',
                    'required' => false,
                    'mapped' => false,
                    'data' => $data,
                    'empty_data' => null,
                    'attr' => array(
                        'placeholder' => 'ポイント計算時に使用する付与率（ 商品毎の設定値で計算 （％））例. 1',
                    ),
                    'constraints' => array(
                        new Assert\Regex(
                            array(
                                'pattern' => "/^\d+$/u",
                                'message' => 'form.type.numeric.invalid',
                            )
                        ),
                    ),
                )
            );
    }

    /**
     * 本クラスでは処理なし
     * @param Request $request
     * @param Response $response
     */
    public function renderView(Request $request, Response $response)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * 本クラスでは処理なし
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * 商品毎ポイント付与率保存
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        // フォーム情報取得処理
        $form = $event->getArgument('form');

        if(empty($form)){
            return false;
        }

        // ポイント付与率取得
        $pointRate = 0;
        $pointRate = $form->get('plg_point_product_rate')->getData();

        // 商品ID取得
        $productId = 0;
        $productId = $form->getData()->getId();

        // 前回入力値と比較
        $status = false;
        $status = $this->app['eccube.plugin.point.repository.pointproductrate']->isSamePoint($pointRate, $productId);


        // 前回入力値と同じ値であれば登録をキャンセル
        if ($status) {
            return true;
        }

        // プロダクトエンティティを取得
        $product = $event->getArgument('Product');

        if(empty($product)){
            return false;
        }

        // ポイント付与保存処理
        $this->app['eccube.plugin.point.repository.pointproductrate']->savePointProductRate($pointRate, $product);
    }
}
