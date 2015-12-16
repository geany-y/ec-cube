<?php
namespace Plugin\GiftWrapping\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class GiftWrappingController
{

    /**
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

            return $app->redirect($app->url('cart'));

    }

}
