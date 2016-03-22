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


namespace Plugin\Point\Resource\lib\EventRoutineWorksHelper\WorkPlace;

use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Plugin\Point\Entity\PointInfo;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : マイページ
 *  - 拡張項目 : 画面表示
 */
class FrontMyPage extends AbstractWorkPlace
{
    /**
     * 本クラスでは処理なし
     * @param FormBuilder $builder
     * @param Request $request
     */
    public function createForm(FormBuilder $builder, Request $request)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * 本クラスでは処理なし
     * @param Request $request
     * @param Response $response
     */
    public function renderView(Request $request, Response $response)
    {
        throw new MethodNotAllowedException();
    }

    /**
     * 本クラスでは処理なし
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();

        $point_rate = 0;
        if(!empty($pointInfo))
        {
            $point_rate = (integer)$pointInfo->getPlgPointConversionRate();
        }

        // ポイント計算ヘルパーを取得
        $calculator = null;
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // ヘルパーの取得判定
        if(empty($calculator)){
            return false;
        }

        // カスタマー情報を取得
        $customer = $this->app['security']->getToken()->getUser();

        if(empty($customer)){
            return false;
        }

        if(empty($pointInfo)){
            return false;
        }

        // 計算に必要なエンティティを登録
        //$calculationService->addEntity($order);
        $calculator->addEntity('Customer', $customer);
        //$calculator->addEntity($pointInfo);

        // 会員保有ポイントを取得
        $currentPoint = $calculator->getPoint();

        // 会員保有ポイント取得判定
        if(empty($currentPoint)){
            $currentPoint = 0;
        }

        // 仮ポイント取得
        $previsionAddPoint = $calculator->getProvisionalAddPoint();

        // 仮ポイント取得判定
        if(empty($previsionAddPoint)){
            $previsionAddPoint = 0;
        }

        // 使用ポイントボタン付与
        // twigコードにポイント表示欄を追加
        $snipet = $this->createHtmlDisplayPointFormat();
        $search = '<div id="history_list"';
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        // ポイント表示用変数作成
        $point = array();
        $point['current'] = $currentPoint;
        $point['pre'] = $previsionAddPoint;
        $point['rate'] = $point_rate;

        // twigパラメータにポイント情報を追加
        $parameters = $event->getParameters();
        $parameters['point'] = $point;
        $event->setParameters($parameters);
    }

    /**
     * マイページポイント表示HTML生成
     * @return string
     */
    protected function createHtmlDisplayPointFormat()
    {
        return <<<EOHTML
<div class="message_box">
    <p>
        現在の保有ポイントは<span class="text-primary">&nbsp;{{ point.current }}pt&nbsp;</span>です<br />
        現在の仮ポイントは<span class="text-primary">&nbsp;{{ point.pre }}pt&nbsp;</span>です<br />
        ※1pt<span class="text-primary">&nbsp;{{ point.rate }}円&nbsp;</span>でご利用いただけます
    </p>
</div>
EOHTML;
    }


    /**
     * ポイントデータの保存
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        throw new MethodNotAllowedException();
    }
}
