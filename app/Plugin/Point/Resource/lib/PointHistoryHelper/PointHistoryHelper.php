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


namespace Plugin\Point\Resource\lib\PointHistoryHelper;

use Doctrine\ORM\Mapping\Entity;
use Plugin\Point\Entity\PointSnapshot;
use Plugin\Point\Entity\Point;

/**
 * ポイント履歴ヘルパー
 * Class PointHistoryHelper
 * @package Plugin\Point\Resource\lib\PointHistoryHelper
 */
class PointHistoryHelper
{
    const HISTORY_MESSAGE_MANUAL_EDIT = 'ポイント手動編集';
    const HISTORY_MESSAGE_EDIT = 'ポイント購入完了登録';
    const HISTORY_MESSAGE_ORDER_EDIT = 'ポイント管理画面受注ステータス変更保存';
    const HISTORY_MESSAGE_ORDER_CANCEL = 'ポイント管理画面受注ステータスキャンセル保存';

    const HISTORY_MESSAGE_TYPE_CURRENT = '保有';
    const HISTORY_MESSAGE_TYPE_PRE_ADD = '付与(仮)';
    const HISTORY_MESSAGE_TYPE_ADD = '付与(確定)';
    const HISTORY_MESSAGE_TYPE_USE = '利用';
    const HISTORY_MESSAGE_TYPE_ADJUST_USE = '利用調整';

    const STATE_CURRENT = 1;
    const STATE_PRE_ADD = 2;
    const STATE_ADD = 3;
    const STATE_USE = 4;
    const STATE_ADJUST_USE = 5;

    protected $app;
    protected $entities;
    protected $currentActionName;
    protected $historyType;
    protected $historyActionType;

    /**
     * PointHistoryHelper constructor.
     */
    public function __construct()
    {
        $this->app = \Eccube\Application::getInstance();
        $this->refreshEntity();
    }

    /**
     * 履歴保存エンティティを新規作成
     */
    public function refreshEntity()
    {
        $this->entities = array();
        $this->entities['SnapShot'] = new PointSnapshot();
        $this->entities['Point'] = new Point();
        $this->entities['PointInfo'] = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
    }

    /**
     * 計算に必要なエンティティを追加
     * @param $entity
     */
    public function addEntity($entity)
    {
        $entityName = explode(DIRECTORY_SEPARATOR, get_class($entity));
        $this->entities[array_pop($entityName)] = $entity;

        return;
    }

    /**
     * 保持エンティティを返却
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * キーをもとに該当エンティティを削除
     * @param string $targetName
     */
    public function removeEntity($targetName)
    {
        if (in_array($targetName, $this->entities[$targetName], true)) {
            unset($this->entities[$targetName]);
        }

        return;
    }

    /**
     * 仮ポイント付与情報を履歴登録
     * @param $point
     */
    public function saveProvisionalAddPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_PRE_ADD;
        $this->historyType = self::STATE_PRE_ADD;
        $this->saveHistoryPoint((0 - $point));
    }

        /**
         * 仮ポイント付与情報を確定し履歴登録
         *  - ポイント戻し処理を行ってから今回調整ポイントを付与
         * @param $point
         */
        public function saveFixProvisionalAddPoint($point)
    {
        // 最終設定ポイント戻し処理
        $this->fixProvisionalAddPoint($point);
        $this->currentActionName = self::HISTORY_MESSAGE_ORDER_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADD;
        $this->historyType = self::STATE_ADD;
        $this->saveHistoryPoint($point);
    }

        /**
         * ポイント付与情報を履歴登録
         * @param $point
         */
        public function saveAddPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADD;
        $this->historyType = self::STATE_ADD;
        $this->saveHistoryPoint($point);
    }

    /**
     *
     */
    public function saveAfterAddCurrentPoint()
    {
        //$this->currentactionname = ;
    }

    /**
     * 商品購入後、現在ポイント履歴登録
     * @param $point
     */
    public function saveAfterShoppingCurrentPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_CURRENT;
        $this->historyType = self::STATE_CURRENT;
        $this->saveHistoryPoint($point);
    }

    /**
     * 受注ステータス変更後仮ポイント確定時、現在ポイント履歴登録
     * @param $point
     */
    public function saveAfterChangeOrderStatusCurrentPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_ORDER_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_CURRENT;
        $this->historyType = self::STATE_CURRENT;
        $this->saveHistoryPoint($point);
    }

    /**
     * 利用ポイント履歴登録
     * @param $point
     */
    public function saveUsePoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_USE;
        $this->historyType = self::STATE_USE;
        $this->saveHistoryPoint($point);
    }

    /**
     * 手動登録(管理者)ポイント履歴登録
     * @param $point
     */
    public function saveManualPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_MANUAL_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_CURRENT;
        $this->historyType = self::STATE_CURRENT;
        $this->saveHistoryPoint($point);
        $this->refreshEntity();
        $this->saveHistoryPoint((0 - $point));

    }

    /**
     * 受注画面利用ポイント調整保存
     * @param $point
     */
    public function saveUsePointAdjustOrderHistory($point)
    {
        $latestUseAdjustPoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($this->entities['Order']);
        $this->currentActionName = self::HISTORY_MESSAGE_MANUAL_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADJUST_USE;
        $this->historyType = self::STATE_ADJUST_USE;
        $this->saveHistoryPoint((0 - $point));
    }

    /**
     * キャンセルポイント履歴登録
     */
    public function saveCancelPoint()
    {
    }

    /**
     * 仮ポイント確定処理
     *  - 最終設定の利用調整ポイントの戻し処理
     * @param $point
     */
    protected function fixProvisionalAddPoint()
    {
        //$this->refreshEntity();
        // 最終利用ポイントを取得
        $lastUsePoint = $this->app['eccube.plugin.point.repository.point']->getLastAdjustUsePoint($this->entities['Order']);
        if(!empty($lastUsePoint)){
            $lastUsePoint = 0;
        }

        $this->currentActionName = self::HISTORY_MESSAGE_MANUAL_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADJUST_USE;
        $this->historyType = self::STATE_ADJUST_USE;
        $this->saveHistoryPoint(0 - $lastUsePoint);
    }

    /**
     * 履歴登録汎用処理
     * @param $point
     */
    protected function saveHistoryPoint($point)
    {
        if (isset($this->entities['Order'])) {
            $this->entities['Point']->setOrder($this->entities['Order']);
        }
        $this->entities['Point']->setPlgPointId(null);
        $this->entities['Point']->setCustomer($this->entities['Customer']);
        $this->entities['Point']->setPointInfo($this->entities['PointInfo']);
        $this->entities['Point']->setPlgDynamicPoint($point);
        $this->entities['Point']->setPlgPointActionName($this->historyActionType.$this->currentActionName);
        $this->entities['Point']->setPlgPointType($this->historyType);

        $this->app['orm.em']->persist($this->entities['Point']);
        $this->app['orm.em']->flush($this->entities['Point']);
        $this->app['orm.em']->clear($this->entities['Point']);
    }

    /**
     * スナップショット情報登録
     * @param $point
     */
    public function saveSnapShot($point)
    {
        $this->entities['SnapShot']->setPlgPointSnapshotId(null);
        $this->entities['SnapShot']->setCustomer($this->entities['Customer']);
        $this->entities['SnapShot']->setPlgPointAdd($point['add']);
        $this->entities['SnapShot']->setPlgPointCurrent($point['current']);
        $this->entities['SnapShot']->setPlgPointUse($point['use']);
        $this->entities['SnapShot']->setPlgPointSnapActionName($this->currentActionName);
        $this->app['orm.em']->persist($this->entities['SnapShot']);
        $this->app['orm.em']->flush($this->entities['SnapShot']);
    }

}
