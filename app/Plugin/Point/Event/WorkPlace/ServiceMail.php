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
 *  - 拡張元 : 受注メール
 *  - 拡張項目 : メール内容
 * Class ServiceMail
 * @package Plugin\Point\Event\WorkPlace
 */
class ServiceMail extends AbstractWorkPlace
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
        throw new MethodNotAllowedException();
    }

    /**
     * メール本文の置き換え
     * @param EventArgs $event
     * @return bool
     */
    public function save(EventArgs $event)
    {
        // 基本情報の取得
        $message = $event->getArgument('message');
        $order = $event->getArgument('Order');
        $mailTemplate = $event->getArgument('MailTemplate');

        // 必要情報判定
        if(!empty($message) || !empty($order)){
            return false;
        }

        if(empty($order->getCustomer)){
            return false;
        }

        // 計算ヘルパーの取得
        $calculator = $this->app['eccube.plugin.point.calculate.helper.factory'];

        // 利用ポイントの取得と設定
        $usePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($order);
        if(!empty($usePoint) && $usePoint != 0){
            $calculator->setUsePoint($usePoint);
        }

        // 計算に必要なエンティティの設定
        $calculator->addEntity('Order',  $order);
        $calculator->addEntity('Customer',  $order->getCustomer());

        // 計算値取得
        $addPoint = $calculator->getAddPointByOrder();
        $point = $calculator->getPoint();
        $amount = $calculator->getTotalAmount();

        // ポイント配列作成
        $pointMessage = array();
        $pointMessage['add'] =  $addPoint;
        $pointMessage['point'] = $point;

        // オーダー情報更新
        $order->setPaymentTotal($amount);
        $order->setTotal($amount);

        // メールボディ取得
        $body = $this->app->renderView($mailTemplate->getFileName(), array(
            'header' => $mailTemplate->getHeader(),
            'footer' => $mailTemplate->getFooter(),
            'Order' => $order,
        ));

        // 情報置換用のキーを取得
        $search = array();
        preg_match_all('/合　計 ¥ .*\\n/u', $body, $search);

        // メール本文置換
        $snipet = $this->createPointMailMessage($pointMessage);
        $replace = $snipet.$search[0][0];
        $body = preg_replace('/'.$search[0][0].'/u', $replace, $body);

        // メッセージにメールボディをセット
        $message->setBody($body);
    }

    protected function createPointMailMessage($pointMessage){
        $message = '付与予定ポイント :'.$pointMessage['add'].PHP_EOL;
        $message.= '現在保有ポイント :'.$pointMessage['point'].PHP_EOL;
        return $message;
    }
}
