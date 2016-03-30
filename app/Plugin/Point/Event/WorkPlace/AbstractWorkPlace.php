<?php


namespace Plugin\Point\Event\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * フックポイント定型処理コンポジションスーパークラス
 * Class AbstractWorkPlace
 * @package Plugin\Point\Event\WorkPlace
 */
abstract class AbstractWorkPlace
{
    /** @var \Eccube\Application */
    protected $app;

    /**
     * AbstractWorkPlace constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * フォーム拡張処理
     * @param FormBuilder $builder
     * @param Request $request
     * @return mixed
     */
    abstract public function createForm(FormBuilder $builder, Request $request);

    /**
     * レンダリング拡張処理
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    abstract public function renderView(Request $request, Response $response);

    /**
     * Twig拡張処理
     * @param TemplateEvent $event
     * @return mixed
     */
    abstract public function createTwig(TemplateEvent $event);

    /**
     * 保存拡張処理
     * @param EventArgs $event
     * @return mixed
     */
    abstract public function save(EventArgs $event);

    /**
     * ビューをsearchをキーにsnippetと置き換え返却
     * @param TemplateEvent $event
     * @param $snippet
     * @param $search
     * @return bool
     */
    protected function replaceView(TemplateEvent $event, $snippet, $search)
    {
        // 必要値を判定
        if (empty($event)) {
            return false;
        }
        if (empty($snippet)) {
            return false;
        }
        if (empty($search)) {
            return false;
        }

        // Twig書き換え
        $replace = $snippet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        return true;
    }
}
