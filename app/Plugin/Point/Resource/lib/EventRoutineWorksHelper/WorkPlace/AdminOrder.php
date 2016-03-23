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
use Plugin\Point\Entity\PointUse;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * フックポイント汎用処理具象クラス
 *  - 拡張元 : 受注登録( 編集 )
 *  - 拡張項目 : ポイント付与判定・登録
 * Class AdminOrder
 * @package Plugin\Point\Resource\lib\EventRoutineWorksHelper\WorkPlace
 */
class  AdminOrder extends AbstractWorkPlace
{
    protected $pointInfo;
    protected $pointType;
    protected $targetOrder;
    protected $calculateCurrentPoint;
    protected $customer;
    protected $calculator;
    protected $history;
    protected $usePoint;

    public function __construct()
    {
        parent::__construct();
        // 履歴管理ヘルパー
        $this->history = $this->app['eccube.plugin.point.history.service'];


        // ポイント情報基本設定を取得
        $this->pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        if(empty($this->pointInfo)){
            return false;
        }
        $this->pointType = $this->pointInfo->getPlgCalculationType();

        // 計算ヘルパー取得
        $this->calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];
    }

    /**
     * フォームを拡張
     *  -   利用ポイント項目を追加
     *  -   追加項目の位置はTwig拡張で配備
     * @param FormBuilder $builder
     * @param Request $request
     */
    public function createForm(FormBuilder $builder, Request $request)
    {
        $order = $builder->getData();

        if(empty($order) || !preg_match('/Order/', get_class($order))){
            return false;
        }

        $hasCustomer = $order->getCustomer();

        // 初期値・取得値設定処理
        $lastUsePoint = 0;
        if(!empty($order) && !empty($hasCustomer)){
            $lastUsePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($order);

            // 調整ポイント計算
            if(empty($lastUsePoint)) {
                $lastUsePoint = 0;
            }
        }

        // ポイント付与率項目拡張
        $builder->add(
            'plg_use_point',
            'text',
            array(
                'label' => '利用ポイント',
                'required' => false,
                'mapped' => false,
                'data' => abs($lastUsePoint),
                'empty_data' => null,
                'attr' => array(
                    'placeholder' => '10 ( 正の整数 )',
                ),
                'constraints' => array(
                    new Assert\Regex(
                        array(
                            'pattern' => "/^\d+?$/u",
                            'message' => 'form.type.numeric.invalid',
                        )
                    ),
                ),
            )
        );
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
     * Twigの拡張
     *  - フォーム追加項目を挿入
     * @param TemplateEvent $event
     */
    public function createTwig(TemplateEvent $event)
    {
        // ポイント情報基本設定を取得
        if(empty($this->pointInfo)){
            return false;
        }

        // オーダーエンティティを取得
        $args = $event->getParameters();
        // オーダーが取得できない際
        if(!isset($args['Order'])){
            return false;
        }

        $order = $args['Order'];
        $hasCustomer = $order->getCustomer();

        if(empty($hasCustomer)){
            return false;
        }

        // 利用ポイントをエンティティにセット
        $pointUse = new PointUse();
        $usePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($order);

        if(empty($usePoint)){
            $usePoint = 0;
        }

        // 利用ポイントを格納
        $pointUse->setPlgUsePoint($usePoint);

        // ポイント基本設定項目の取得
        /*
        $pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        */
        // ポイント基本設定の確認
        if(empty($this->pointInfo)){
            return false;
        }

        // 計算種別判定 ( 利用ポイント減算 あり/なし )
        /*
        $calcType = null;
        $calculationService = null;
        */

        /*
        if ($this->pointInfo->getPlgCalculationType() == PointInfo::POINT_CALCULATE_SUBTRACTION) {
            // 利用ポイント減算処理
            $calcType = PointInfo::POINT_CALCULATE_SUBTRACTION;
        } else {
            if ($pointInfo->getPlgCalculationType() == PointInfo::POINT_CALCULATE_NORMAL) {
                // 利用ポイント減算なし
                $calcType = PointInfo::POINT_CALCULATE_NORMAL;
            }
        }
        */

        //$calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 計算ヘルパー取得判定
        if (is_null($this->calculator)) {
            return true;
        }

        // 計算に必要なエンティティを登録
        $this->calculator->addEntity('Order', $order);
        $this->calculator->addEntity('Customer', $order->getCustomer());
        $this->calculator->setUsePoint($usePoint);

        // 付与ポイント取得
        $addPoint = $this->calculator->getAddPointByOrder();

        //付与ポイント取得可否判定
        if (is_null($addPoint)) {
            return true;
        }

        // 現在保有ポイント取得
        $currentPoint = $this->calculator->getPoint();

        //保有ポイント取得可否判定
        if (is_null($currentPoint)) {
            $currentPoint = 0;
        }

        // twigコードに利用ポイントを挿入
        // 会員情報に保有ポイントを表示
        $snipet = $this->createHtmlCustomerCurrentPoint();
        $search = '<div id="customer_info_list__message"';
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        // 受注商品情報に受注ポイント情報を表示
        $snipet = $this->createHtmlDisplayOrderPoint();
        $search = '<dl id="product_info_result_box__body_summary"';
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        // ポイント表示用変数作成
        $point = array();
        $point['current'] = $currentPoint;
        $point['use'] = $usePoint;
        $point['add'] = $addPoint;

        // twigパラメータにポイント情報を追加
        $parameters = $event->getParameters();
        $parameters['point'] = $point;
        $event->setParameters($parameters);
    }

    /**
     * 受注確認画面会員保有ポイントHTML生成
     * @return string
     */
    protected function createHtmlCustomerCurrentPoint()
    {
        return <<<EOHTML
<div id="customer_info_list__current_point" class="form-group">
<label class="col-sm-2 control-label">保有ポイント</label>
<div class="col-sm-9 col-lg-10">
<p>{{ point.current }}pt</p>
</div>
</div>
EOHTML;
    }

    /**
     * 受注確認画面受注情報表示ポイントHTML生成
     * @return string
     */
    protected function createHtmlDisplayOrderPoint()
    {
        return <<<EOHTML
<dl id="product_info_result_box__point_summary" class="dl-horizontal">
<dt id="product_info_result_box__point_add">利用ポイント&nbsp;:</dt>
<dd>{{ form_widget(form.plg_use_point) }}&nbsp;pt</dd>
<dt id="product_info_result_box__point_add">付与ポイント&nbsp;:</dt>
<dd>{{ point.add }}&nbsp;pt</dd>
</dl>
EOHTML;
    }

    /**
     * 受注ステータス判定・ポイント更新
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        // ポイント取得
        $this->usePoint = $event->getArgument('form')->get('plg_use_point')->getData();
        // 利用ポイント確認
        if(empty($this->usePoint)){
            $this->usePoint = 0;
        }

        // 必要情報をセット
        //$this->pointInfo = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
        $this->targetOrder = $event->getArgument('TargetOrder');
        $this->customer = $event->getArgument('Customer');

        if(empty($this->targetOrder)){
            return false;
        }

        if(empty($this->customer)){
            return false;
        }

        if(!empty($this->targetOrder) && !empty($this->customer)){
            $this->calculator->addEntity('Order', $this->targetOrder);
            $this->calculator->addEntity('Customer', $this->customer);
            $addPoint = $this->calculator->getAddPointByOrder();

            if(!empty($addPoint)){
                $provisionalPoint = $this->app['eccube.plugin.point.repository.point']->getProvisionalAddPointByOrder($this->targetOrder);
                // 現在仮付与ポイントと保存済み付与ポイントに相違があった際はアップデート処理
                if($provisionalPoint != $addPoint){
                    $this->updateOrderEvent($addPoint, $provisionalPoint);
                }
            }
        }

        // 以下受注画面内、イベント処理
        // 受注ステータス判定→ポイント確定処理
        if ($this->targetOrder->getOrderStatus()->getId() == $this->pointInfo->getPlgAddPointStatus()) {
            $this->pointFixEvent($event);
        }

        // 利用ポイント調整イベント
        //if(!empty($this->usePoint)){
        $this->pointUseEvent($event);
        //}

        $this->calculator->setUsePoint($this->usePoint);
        $total = $addPoint = $this->calculator->getTotalAmount();
        $this->targetOrder->setTotal($total);
        $this->targetOrder->setPaymentTotal($total);

        $this->app['orm.em']->persist($this->targetOrder);
        $this->app['orm.em']->flush($this->targetOrder);
    }

    public function updateOrderEvent($addPoint, $provisionalPoint){
        if(empty($addPoint)){
            return false;
        }

        if(!empty($provisionalPoint)) {
            $this->history->addEntity($this->targetOrder);
            $this->history->addEntity($this->customer);
            $this->history->saveProvisionalAddPoint(abs($provisionalPoint) * -1);
        }

        // 履歴保存
        // 仮ポイントの保存
        $this->history->refreshEntity();
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        $this->history->saveProvisionalAddPoint(abs($addPoint));

        $point = array();
        $this->refreshCurrentPoint();
        $point['current'] = $this->calculateCurrentPoint;
        $point['use'] = 0;
        $point['add'] = $addPoint;

        $this->app['eccube.plugin.point.repository.pointcustomer']->savePoint(
            $this->calculateCurrentPoint,
            $this->customer
        );

        $this->history->refreshEntity();
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        $this->history->saveSnapShot($point);
    }

    /**
     * ポイント確定時処理
     *  -   受注ステータス判定でポイントの付与が確定した際の処理
     * @param $pointInfo
     * @return bool
     */
    protected function pointFixEvent($pointInfo)
    {
        // 本受注最終受注ポイントの種類を判定する
        $fixFlg = $this->app['eccube.plugin.point.repository.point']->isLastProvisionalFix($this->targetOrder);

        if($fixFlg){
            return false;
        }

        if(empty($pointInfo)){
            return false;
        }

        if(empty($this->targetOrder)){
            return false;
        }

        if(empty($this->customer)){
            return false;
        }

        //$res = $this->app['eccube.repository.order']->findOneById($this->targetOrder);

        // 仮付与ポイントがあるか確認
        $provisionalPoint = $this->app['eccube.plugin.point.repository.point']->getProvisionalAddPointByOrder($this->targetOrder);

        if(empty($provisionalPoint)){
            return false;
        }

        // 仮付与確定があれば処理キャンセル
        /*
        $fixProvisionalPoint = $this->app['eccube.plugin.point.repository.point']->getFixProvisionalAddPointByOrder($this->targetOrder);
        if(!empty($fixProvisionalPoint)){
            return false;
        }
        */


        // AdminOrder計算ヘルパーを使用
        //$this->calculator->addEntity($pointInfo);
        $this->calculator->addEntity('Order', $this->targetOrder);
        $this->calculator->addEntity('Customer', $this->customer);


        // 履歴保存
        // 仮ポイントの保存
        $this->saveFixOrderHistory($provisionalPoint);

        $point = array();
        $this->refreshCurrentPoint();
        $point['current'] = $this->calculateCurrentPoint;
        $point['use'] = 0;
        $point['add'] = $provisionalPoint;

        // SnapShot保存
        $this->saveFixOrderSnapShot($point);
    }

    protected function isSameUsePoint($lastUse){
        if($lastUse == $this->usePoint){
            return true;
        }
        return false;
    }

    /**
     * 利用ポイントイベント
     * @param $event
     * @param $pointInfo
     * @return bool
     */
    protected function pointUseEvent($event)
    {
        $lastUsePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($this->targetOrder);

        // 最終利用ポイント確認
        if(empty($lastUsePoint)) {
            $lastUsePoint = 0;
        }

        // 最終利用ポイントと現在利用ポイントが同じであれば処理をキャンセル
        if($this->isSameUsePoint($lastUsePoint)){
            return false;
        }


        // 現在利用ポイントを設定
        $calculateUsePoint = $lastUsePoint - $this->usePoint;
        $calculateUsePoint = $calculateUsePoint * -1;

        // 計算に使用する基本情報の設定
        $pointUse = new PointUse();


        // 計算に必要なエンティティをセット
        $this->calculator->addEntity('Order', $this->targetOrder);
        $this->calculator->addEntity('Customer', $this->customer);
        // 計算使用値は絶対値
        $this->calculator->setUsePoint(abs($calculateUsePoint));


        // 付与ポイント取得
        $addPoint = $this->calculator->getAddPointByOrder();

        // 履歴保存
        // 戻し
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        // 戻しは以前のポイント
        $this->history->saveUsePointAdjustOrderHistory(abs($lastUsePoint));
        // 入力
        $this->history->refreshEntity();
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        $this->history->saveUsePointAdjustOrderHistory(abs($this->usePoint) * -1);

        // 現在保有ポイント取得
        $this->refreshCurrentPoint();
        $currentPoint = $this->calculateCurrentPoint;
        if(empty($currentPoint)){
            $currentPoint = 0;
        }

        $point = array();
        // 現在ポイントをログから再計算
        $point['current'] = $currentPoint;
        $point['use'] = $calculateUsePoint;
        // 計算付与ポイント
        $point['add'] = $addPoint;

        $this->app['eccube.plugin.point.repository.pointcustomer']->savePoint(
            $currentPoint,
            $this->customer
        );

        // SnapShot保存
        $this->saveAdjustUseOrderSnapShot($point);
    }

    //
    /**
     * ポイント確定情報の保存
     *  - 利用調整ポイントの戻しと追加
     * @param $provisionalPoint
     * @return bool
     */
    protected function saveFixOrderHistory($provisionalPoint)
    {
        if(empty($this->targetOrder)){
            return false;
        }

        if(empty($this->customer)){
            return false;
        }

        if(empty($provisionalPoint)){
            return false;
        }

        // 履歴情報登録
        // 利用ポイント
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        $this->history->fixProvisionalAddPoint($provisionalPoint);
        $this->history->refreshEntity();
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        $this->history->saveFixProvisionalAddPoint($provisionalPoint);

        // 会員ポイント更新
        // 現在ポイントを履歴から計算
        $this->refreshCurrentPoint();
        $this->app['eccube.plugin.point.repository.pointcustomer']->savePoint(
            $this->calculateCurrentPoint,
            $this->customer
        );
    }

    /**
     * スナップショットテーブルへの保存
     * @param $point
     */
    protected function saveAdjustUseOrderSnapShot($point)
    {
        if(empty($this->targetOrder)){
            return false;
        }

        if(empty($this->customer)){
            return false;
        }
        // ポイント保存用変数作成 @todo ここのaddの仮ポイントをどうするか
        $this->history->refreshEntity();
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        $this->history->saveSnapShot($point);
    }

    /**
     * スナップショットテーブルへの保存
     * @param $point
     */
    protected function saveFixOrderSnapShot($point)
    {
        if(empty($this->targetOrder)){
            return false;
        }

        if(empty($this->customer)){
            return false;
        }
        // ポイント保存用変数作成 @todo ここのaddの仮ポイントをどうするか
        $this->history->refreshEntity();
        $this->history->addEntity($this->targetOrder);
        $this->history->addEntity($this->customer);
        $this->history->saveSnapShot($point);
    }

    /**
     * 現在ポイントをログから再計算
     */
    protected function refreshCurrentPoint()
    {
        $this->calculateCurrentPoint = $this->app['eccube.plugin.point.repository.point']->getCalculateCurrentPointByCustomerId(
            $this->customer->getId()
        );
    }

}
