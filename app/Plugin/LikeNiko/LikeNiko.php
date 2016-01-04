<?php
namespace Plugin\LikeNiko;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Plugin\LikeNiko\Entity\NikoInfo;

class LikeNiko
{
    /** @var  \Eccube\Application $app */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * トップページにコメント送信フォームを追記
     *
     * @param FilterResponseEvent $event
     */
    public function setNikoMassegeBox(FilterResponseEvent $event)
    {
        // 基本情報エラーハンドリング
        $nikoinfo = $this->app['eccube.plugin.likeniko.repository.nikonfo']->findAll();
        if (!isset($nikoinfo[0])) {
            return true;
        }
        $nikoinfo = $nikoinfo[0];

        // Nodeサイトチェック
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $nikoinfo->getNodeServerAddress());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        curl_close($ch); 

        if (empty($output)) {
            return true;
        }


        $request = $event->getRequest();
        $response = $event->getResponse();

        $html = $this->getHtmlLikeNiko($request, $response, $nikoinfo);
        $response->setContent($html);

        $event->setResponse($response);
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param LikeNiko\Entity\Nikoinfo $info
     * @return string
     */
    private function getHtmlLikeNiko(Request $request, Response $response, $info)
    {
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);

        $form = $this->app['form.factory']->createBuilder('likeniko')->getForm();
        $form->handleRequest($request);

        // 書き込みフォーム取得
        $formParts = $this->app->renderView('LikeNiko/Resource/template/likeniko_form_parts.twig', array(
            'form' => $form->createView(),
        ));

        // ログインユーザーのみ書き込み設定時判定
        if ($info->getIsAuthFlg() == NikoInfo::IS_AUTH_ON && !$this->app->isGranted('ROLE_USER')) {
            $formParts = '';
        }

        // Iframe情報取得
        $iframeParts = $this->app->renderView('LikeNiko/Resource/template/likeniko_iframe_parts.twig', array(
            'info' => $info,
        ));

        // js情報取得
        $jsParts = $this->app->renderView('LikeNiko/Resource/template/likeniko_js_parts.twig', array());

        try {
            // 書き込みフォーム
            $oldHtml = $crawler->filter($info->getFormInsertKey())->first()->html();
            $formHtml = $formParts.$oldHtml;
            $html = str_replace($oldHtml, $formHtml, $html);
            $oldHtml = $crawler->filter($info->getReplaceBlockKey())->parents()->html();
            $html = str_replace($oldHtml, $iframeParts, $html);
            $oldHtml = $crawler->filter('script')->last()->html();
            $jsHtml = $oldHtml.$jsParts;
            $html = str_replace($oldHtml, $jsHtml, $html);
        } catch (\Exception $e) {
            throw new Exception();
        }

        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
     */
    private function getHtml(Crawler $crawler)
    {
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }
        return html_entity_decode($html, ENT_NOQUOTES, 'UTF-8');
    }
}
