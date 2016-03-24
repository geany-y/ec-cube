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


namespace Plugin\Point\Event\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Symfony\Component\DomCrawler\Crawler;
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
    /** @var \Eccube\Application  */
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
}
