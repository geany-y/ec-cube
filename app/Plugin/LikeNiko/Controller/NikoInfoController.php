<?php
//設定画面用(時間に余裕があれば作成)
namespace Plugin\LikeNiko\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;

class NikoInfoController
{
    public function index(Application $app, Request $request)
    {
        $NikoInfo = new \Plugin\LikeNiko\Entity\NikoInfo();
        // エラーハンドリング
        if (!$NikoInfo) {
            throw new NotFoundHttpException();
        }

        $repo = $app['eccube.plugin.likeniko.repository.nikonfo'];
        $EditNikoInfo = $repo->findAll();

        if (!is_null($EditNikoInfo) && count($EditNikoInfo) > 0) {
            $NikoInfo = $EditNikoInfo[0];
        }

        //フォーム生成
        $form = $app['form.factory']
            ->createBuilder('admin_nikoinfo', $NikoInfo)
            ->getForm();

        // 保存処理
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
                $SaveNiko= $form->getData();
                $status = $repo->save($SaveNiko);
                if ($status) {
                    $res = $this->setNodeConfig($SaveNiko, $request->getUriForPath('/'));
                    if ($res) {
                        $app->addSuccess('admin.nikoinfo.save.complete', 'admin');
                        return $app->redirect($app->url('nikoinfo'));
                    }
                }
                $app->addError('admin.nikoinfo.save.error', 'admin');
        }
        
        return $app->render('LikeNiko/Resource/template/admin/nikoinfo.twig', array(
            'form'      => $form->createView(),
        ));
    }

    private function setNodeConfig($sendData, $domain)
    {
        if (!isset($nikoinfo) && empty($sendData)) {
            return false;
        }

        $replace_dev = '';
        $replace_dev = str_replace('/index_dev.php', '',$domain);
        if (!empty($replace_dev)) {
            $domain = $replace_dev;
        }

        // NodeServer送信情報設定
        $datas = array();
        $self_server  = array();
        preg_match('/^(.*):([0-9]+)$/', $sendData->getNodeServerAddress(), $self_server);
        if (count($self_server) < 1) {
            return false;
        }

        $datas['infos']['nodeurl'] = $self_server[1];
        $datas['infos']['port'] = $self_server[2];
        $datas['infos']['canvs_width'] = $sendData->getTargetImgWidth();
        $datas['infos']['canvas_height'] = $sendData->getTargetImgHeight();
        $datas['infos']['base_url'] = $domain;
        $datas['infos']['img'] = $sendData->getTargetImgName();

        //ノードに設定情報を送信
        $url = $sendData->getNodeServerAddress().'/setnikoconf';
        $header = array("Content-Type: application/json; charset=utf-8");
        $options = array('http' => array(
            'method' => 'POST',
            'header' => implode("\r\n", $header),
            //'content' => http_build_query($data)
            'content' => json_encode($datas)
        ));
        try{
            $contents = file_get_contents($url, false, stream_context_create($options));
            return true;
        } catch(\Exception $e) {
            return false;;
        }
    }
}
