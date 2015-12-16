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
use Plugin\Point\Entity\Point;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

class PointController
{
    public function index(Application $app, Request $request)
    {
        //ポイントのエンティティ取得
        $Point = new \Plugin\Point\Entity\PointInfo();
        // エラーハンドリング
        if (!$Point) {
            throw new NotFoundHttpException();
        }

        $repo = $app['eccube.plugin.point.repository.pointinfo'];
        $pointData = $repo->findAll();

        if (!is_null($pointData)) {
            $Point = $pointData[0];
        }

        //フォーム生成
        $form = $app['form.factory']
            ->createBuilder('admin_point', $Point)
            ->getForm();

        // 保存処理
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $SavePoint = $form->getData();
                $status = $repo->save($SavePoint);
                if ($status) {
                    $app->addSuccess('admin.point.save.complete', 'admin');
                    return $app->redirect($app->url('point'));
                } else {
                    $app->addError('admin.point.save.error', 'admin');
                }
            }
        }

        return $app->render('Point/Resource/template/admin/pointinfo.twig', array(
            'form'          => $form->createView(),
            'Point'   => $Point,
        ));
    }
}
