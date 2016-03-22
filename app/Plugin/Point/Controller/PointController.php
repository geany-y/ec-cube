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
namespace Plugin\Point\Controller;

use Eccube\Application;
use Plugin\Point\Entity\PointInfo;
use Plugin\Point\Form\Type;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PointController
 * ポイント設定画面用コントローラー
 * @param \Eccube\Application $app
 * @param \Symfony\Component\HttpFoundation\Request $request
 */
class PointController
{
    /** @var Application */
    protected $app;

    /**
     * PointController constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * ポイント基本情報管理設定画面
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        // 権限判定
        if (!$this->app->isGranted('ROLE_ADMIN') && !$this->app->isGranted('ROLE_USER')) {
            throw new HttpException\NotFoundHttpException();
        }

        // 最終保存のポイント設定情報取得
        $PointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();

        // 既存データがない場合
        if (!isset($PointInfo) || empty($PointInfo)) {
            $PointInfo = new PointInfo();
        }

        //フォーム生成
        $form = $app['form.factory']
            ->createBuilder('admin_point_info', $PointInfo)
            ->getForm();

        // 保存処理
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $saveData = $form->getData();
                $status = $this->app['eccube.plugin.point.repository.pointinfo']->save($saveData);
                if ($status) {
                    $app->addSuccess('admin.point.save.complete', 'admin');

                    return $app->redirect($app->url('point_info'));
                } else {
                    $app->addError('admin.point.save.error', 'admin');
                }
            }
        }

        // フォーム項目名称描画用文字配列
        $orderStatus = array();
        foreach ($this->app['eccube.repository.order_status']->findAllArray() as $id => $node) {
            $orderStatus[$id] = $node['name'];
        }

        return $app->render(
            'Point/Resource/template/admin/pointinfo.twig',
            array(
                'form' => $form->createView(),
                'Point' => $PointInfo,
                'orderStatus' => $orderStatus,
            )
        );
    }

    /**
     * 利用ポイント入力画面
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function usePoint(Application $app, Request $request)
    {
        // 権限判定
        if (!$this->app->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new HttpException\NotFoundHttpException;
        }

        // 最終保存のポイント設定情報取得
        $usePoint = null;
        if ($this->app['session']->has('usePoint')) {
            $usePoint = $this->app['session']->get('usePoint');
        }

        // 現在保有ポイント
        $customer_id = $this->app['security']->getToken()->getUser()->getId();

        $customerPoint = $this->app['eccube.plugin.point.repository.pointcustomer']->getLastPointById($customer_id);

        //フォーム生成
        $form = $this->app['form.factory']
            ->createBuilder()->add(
                'plg_use_point',
                'text',
                array(
                    'label' => '利用ポイント',
                    'required' => false,
                    'mapped' => false,
                    'empty_data' => '',
                    'data' => $usePoint,
                    'attr' => array(
                        'placeholder' => '1 ( 正の整数 )',
                    ),
                    'constraints' => array(
                        new Assert\LessThanOrEqual(array(
                            'value' => $customerPoint,
                            'message' => 'front.point.enter.usepointe.error',
                        )),
                        new Assert\Regex(
                            array(
                                'pattern' => "/^\d+$/u",
                                'message' => 'form.type.numeric.invalid',
                            )
                        ),
                    ),
                )
            )->getForm();


        // 保存処理
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $saveData = $form->get('plg_use_point')->getData();
                $this->app['session']->set('usePoint', $saveData);
                return $this->app->redirect($this->app->url('shopping'));
            }
        }

        // フォーム項目名称描画用文字配
        return $app->render(
            'Point/Resource/template/default/pointuse.twig',
            array(
                'form' => $form->createView(),
                'usePoint' => $usePoint,
            )
        );
    }
}
