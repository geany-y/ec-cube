<?php
namespace Plugin\GiftWrapping\Controller;

use Eccube\Application;
use Plugin\GiftWrapping\Entity\Wrapping;
use Symfony\Component\HttpFoundation\Request;

class ConfigController
{

    /**
     * ラッピング用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        $Wrapping = $app['eccube.plugin.repository.wrapping']->find(1);

        if (!$Wrapping) {
            $Wrapping = new Wrapping();
        }

        $form = $app['form.factory']->createBuilder('giftwrapping_config', $Wrapping)->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $Wrapping = $form->getData();

                // IDは1固定
                $Wrapping->setId(1);

                $app['orm.em']->persist($Wrapping);
                $app['orm.em']->flush();


                $app->addSuccess('admin.gift_wrapping.save.complete', 'admin');

            }

        }

        return $app->render('GiftWrapping/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
            'Wrapping' => $Wrapping,
        ));
    }

}
