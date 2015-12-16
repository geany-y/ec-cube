<?php
namespace Plugin\LikeNiko;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class LikeNiko
{

    /** @var  \Eccube\Application $app */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 購入画面にラッピング項目を表示
     *
     * @param FilterResponseEvent $event
     */
    public function setNikoMassegeBox(FilterResponseEvent $event)
    {

        $request = $event->getRequest();
        $response = $event->getResponse();

        //$Wrapping = $this->app['eccube.plugin.repository.wrapping']->find(1);

        //if ($Wrapping->getIsWrapping()) {
            $html = $this->getHtmlLikeNiko($request, $response);
            $response->setContent($html);
        //}

        $event->setResponse($response);
    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @return string
     */
    private function getHtmlLikeNiko(Request $request, Response $response)
    {

        $crawler = new Crawler($response->getContent());

        $html = $this->getHtml($crawler);

        $form = $this->app['form.factory']->createBuilder('likeniko')->getForm();

        $form->handleRequest($request);

        $parts = $this->app->renderView('LikeNiko/Resource/template/likeniko_parts.twig', array(
            'form' => $form->createView()
        ));

        try {
            $oldHtml = $crawler->filter('#main_middle')->last()->html();
            $visualhtml = $crawler->filter('.main_visual')->html();
            $newHtml = $oldHtml . $parts;
            $buff = str_replace($visualhtml, '', $newHtml);
            $html = str_replace($oldHtml, $buff, $html);

        } catch (\InvalidArgumentException $e) {
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
