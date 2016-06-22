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


namespace Eccube\Controller\Tutorial;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CrudController extends AbstractController
{
    const LIST_ORDER = 'desc';

    /**
     * index
     * 登録画面件、レコードの一覧表示
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $app->clearMessage();

        $Crud = new \Eccube\Entity\Crud();

        $builder = $app['form.factory']->createBuilder('crud', $Crud);

        $form = $builder->getForm();

        $defaultForm = clone $form;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $saveStatus = $app['eccube.repository.crud']->save($Crud);

            if ($saveStatus) {
                $app->addSuccess('データーが保存されました');
                $form = $defaultForm;
            } else {
                $app->addError('データーベースの保存中にエラーが発生いたしました');

            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $app->addError('入力内容をご確認ください');
        }

        $crudList = $app['eccube.repository.crud']->getAllDataSortByUpdateDate(self::LIST_ORDER);

        return $app->render(
            'Tutorial/crud_top.twig',
            array(
                'forms' => $form->createView(),
                'crudList' => $crudList,
            )
        );
    }

    /**
     * 編集画面
     * idを元にレコードを引き当て編集、問題がなければ、登録画面に遷移
     *
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Application $app, Request $request, $id)
    {
        //$app->clearMessage();

        $Crud = $app['eccube.repository.crud']->getDataById($id);

        if (!$Crud) {
            return $app->redirect($app->url('tutorial_crud'));
        }

        $builder = $app['form.factory']->createBuilder('crud', $Crud);
        $builder->remove('save');
        $builder->add(
            'update',
            'submit',
            array(
                'label' => '編集を確定する',
                'attr' => array(
                    'style' => 'float:left;',
                )
            )
        )
        ->add(
            'back',
            'button',
            array(
                'label' => '戻る',
                'attr' => array(
                    'style' => 'float:left;',
                    'onClick' => 'javascript:history.back();'
                )
            )
        );

        $form = $builder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $saveStatus = $app['eccube.repository.crud']->save($Crud);

            if ($saveStatus) {
                $app->addSuccess('データーが保存されました');
                return $app->redirect($app->url('tutorial_crud'));
            } else {
                $app->addError('データーベースの保存中にエラーが発生いたしました');
            }
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $app->addError('入力内容をご確認ください');
        }

        return $app->render(
            'Tutorial/crud_edit.twig',
            array(
                'forms' => $form->createView(),
                'crud' => $Crud,
            )
        );
    }

    /**
     * 削除画面
     * 引数を元に該当レコードを削除
     * 問題がなければ、登録画面に遷移
     *
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);
        $Crud = $app['orm.em']
            ->getRepository('Eccube\Entity\Crud')
            ->find($id);
       if (is_null($Crud)) {
            $app->addError('該当IDのデーターが見つかりません');
            return $app->redirect($app->url('tutorial_crud'));
       }
        $app['orm.em']->remove($Crud);
        $app['orm.em']->flush($Crud);
        return $app->redirect($app->url('tutorial_crud'));
     }
    
}
