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


namespace Plugin\Point\Helper\EventRoutineWorksHelper;

use \Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\Point\Event\WorkPlace\AbstractWorkPlace;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * フックポイント定型処理を保持オブジェクトに移譲
 * Class EventRoutineWorksHelper
 * @package Plugin\Point\Helper\EventRoutineWorksHelper
 */
class EventRoutineWorksHelper
{
    /**
     * @var AbstractWorkPlace
     */
    protected $place;

    /**
     * EventRoutineWorksHelper constructor.
     * @param AbstractWorkPlace $place
     */
    public function __construct(AbstractWorkPlace $place)
    {
        $this->app = \Eccube\Application::getInstance();
        $this->place = $place;
    }

    /**
     * フォーム拡張
     * @param FormBuilder $builder
     * @param Request $request
     * @return mixed
     */
    public function createForm(FormBuilder $builder, Request $request)
    {
        return $this->place->createForm($builder, $request);
    }

    /**
     * 画面描画拡張
     * @param Request $request
     * @param Response $response
     */
    public function renderView(Request $request, Response $response)
    {
        $this->place->renderView($request, $response);
    }

    /**
     * Twig拡張
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        $this->place->createTwig($event);
    }

    /**
     * データ保存拡張
     * @param EventArgs $event
     */
    public function save(EventArgs $event)
    {
        $this->place->save($event);
    }
}
