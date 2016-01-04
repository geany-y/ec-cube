<?php
namespace Plugin\LikeNiko\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

class PublishController
{
    public function index(Application $app, Request $request)
    {
        // 設定情報取得
        $nikoinfo = $app['eccube.plugin.likeniko.repository.nikonfo']->findAll();
        if (!isset($nikoinfo[0])) {
            echo '設定に不足があります';
            exit();
        }
        if (!$request->isXmlHttpRequest()) {
            throw new Exception();
        }
        $nikoinfo = $nikoinfo[0];
        
        $datas = array();
        $datas['orders'] = str_replace('/', '', str_replace("\0", "",strip_tags(sprintf('%20s',$request->get('niko')))));

        //ノードに予約情報をプッシュ
        $url = $nikoinfo->getNodeServerAddress().'/fromorder';
        $header = array("Content-Type: application/json; charset=utf-8");
        $options = array('http' => array(
            'method' => 'POST',
            'header' => implode("\r\n", $header),
            //'content' => http_build_query($data)
            'content' => json_encode($datas)
        ));
        try{
            $contents = file_get_contents($url, false, stream_context_create($options));
            return '';
        } catch(\Exception $e) {
            echo 'サーバーから応答がありません';
        }
        exit();
    }
}
