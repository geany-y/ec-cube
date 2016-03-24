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
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : マイページ履歴表示
 *  - 拡張項目 : 画面表示
 * Class FrontHistory
 * @package Plugin\Point\Event\WorkPlace
 */
class FrontHistory extends AbstractWorkPlace
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
        // 必要情報の取得と判定
        $parameters = $event->getParameters();
        if(!isset($parameters['Order']) || empty($parameters['Order'])){
            return false;
        }

        if(is_null($parameters['Order']->getCustomer())){
            return false;
        }

        // ポイント計算ヘルパーを取得
        $calculator = null;
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // ヘルパーの取得判定
        if(empty($calculator)){
            return false;
        }

        // 利用ポイントの取得と設定
        $usePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($parameters['Order']);

        // 計算に必要なエンティティを登録
        $calculator->addEntity('Order', $parameters['Order']);
        $calculator->addEntity('Customer', $parameters['Order']->getCustomer());
        $calculator->setUsePoint($usePoint);

        // 保有ポイントを取得
        $point = $calculator->getPoint();

        // 保有ポイント取得判定
        if(empty($point)){
            $point = 0;
        }

        // 付与ポイント取得
        $addPoint = $calculator->getAddPointByOrder();

        // 付与ポイント取得判定
        if(empty($addPoint)){
            $addPoint = 0;
        }

        // 合計金額取得
        $amount = $calculator->getTotalAmount();

        // 合計金額取得判定
        if(empty($amount)){
            $amount = 0;
        }

        // 受注情報を更新
        $parameters['Order']->setTotal($amount);
        $parameters['Order']->setPaymentTotal($amount);

        // Twigデータ内IDをキーに表示項目を追加
        // ポイント情報表示
        // false が返却された際は、利用ポイント値が保有ポイント値を超えている
        if ($amount == false) {
            $snipet = $this->createHtmlDisplayPointUseOverErrorFormat();
        } else {
            $snipet = $this->createHtmlDisplayPointFormat();
        }


        // twigコードに利用ポイントを挿入
        $search = '<p id="summary_box__payment_total"';
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        // ポイント表示用変数作成
        $pointCollection = array();
        $pointCollection['current'] = $point;
        // エラー判定
        // false が返却された際は、利用ポイント値が保有ポイント値を超えている
        if ($amount == false) {
            $point['use_error'] = 'front.point.display.usepointe.error';
        } else {
            $pointCollection['use'] = $usePoint;
        }
        $pointCollection['add'] = $addPoint;

        // twigパラメータにポイント情報を追加
        $parameters = $event->getParameters();
        $parameters['point'] = $pointCollection;
        $event->setParameters($parameters);
    }

    /**
     * マイページ履歴画面ポイント表示HTML生成
     * @return string
     */
    protected function createHtmlDisplayPointFormat()
    {
        return <<<EOHTML
<br />
<dl id = "summary_box__customer_point">
<dt class="text-primary">現在のポイント</dt>
<dd>{{ point.current }}</dd>
</dl>
<dl id = "summary_box__customer_point">
<dt class="text-primary">今回利用ポイント</dt>
<dd class="text-primary">{{ point.use }}</dd>
</dl>
<dl id = "summary_box__customer_point">
<dt class="text-primary">付与予定ポイント</dt>
<dd>{{ point.add }}</dd>
</dl>
EOHTML;
    }

    /**
     * マイページ履歴画面ポイント表示HTML生成
     *  - エラー表示
     * @return string
     */
    protected function createHtmlDisplayPointUseOverErrorFormat()
    {
        return <<<EOHTML
<br />
<dl id = "summary_box__customer_point">
<dt class="text-primary">現在のポイント</dt>
<dd>{{ point.current }}</dd>
</dl>
<dl id = "summary_box__customer_point">
<dt class="text-primary">今回利用ポイント</dt>
<dd class="text-primary">{{ point.use_error }}</dd>
</dl>
<dl id = "summary_box__customer_point">
<dt class="text-primary">付与予定ポイント</dt>
<dd>{{ point.add }}</dd>
</dl>
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
