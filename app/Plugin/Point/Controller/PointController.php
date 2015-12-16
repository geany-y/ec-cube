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
    public function __construct()
    {
    }

    public function index(Application $app, Request $request)
    {
        $repo = $app['eccube.plugin.point.repository.pointinfo'];

        $Point = new \Plugin\Point\Entity\PointInfo();

        if (!$Point) {
            throw new NotFoundHttpException();
        }

        $form = $app['form.factory']
            ->createBuilder('admin_point', $Point)
            ->getForm();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $SavePoint = $form->getData();
                $status = $repos->save($Point);
                if ($status) {
                    $app->addSuccess('admin.point.save.complete', 'admin');
                    return $app->redirect($app->url('point'));
                } else {
                    $app->addError('admin.point.save.error', 'admin');
                }
            }
        }

        //$Makers = $app['eccube.plugin.maker.repository.maker']->findAll();
        return $app->render('Point/View/admin/index.twig', array(
            'form'          => $form->createView(),
            //'Makers'        => $Makers,
            'Point'   => $Point,
        ));
    }
}
