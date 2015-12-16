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

//設定画面用(時間に余裕があれば作成)
namespace Plugin\LikeNiko\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

class PublishController
{
    public function __construct()
    {
    }

    public function index(Application $app, Request $request)
    {
        $datas = array();
        $datas['orders'] = $request->get('niko');
        //ノードに予約情報をプッシュ
        $url = 'http://www.les-tournesol.com:7960/fromorder';
        $header = array("Content-Type: application/json; charset=utf-8");
        $options = array('http' => array(
            'method' => 'POST',
            'header' => implode("\r\n", $header),
            //'content' => http_build_query($data)
            'content' => json_encode($datas)
        ));
        $contents = file_get_contents($url, false, stream_context_create($options));
        return true;
        exit();
    }
}
