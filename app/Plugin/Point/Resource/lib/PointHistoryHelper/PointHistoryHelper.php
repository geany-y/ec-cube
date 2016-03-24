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

use Plugin\Point\Entity\PointSnapshot;
use Plugin\Point\Entity\Point;

/**
 * ポイント履歴ヘルパー
 * Class PointHistoryHelper
 * @package Plugin\Point\Resource\lib\PointHistoryHelper
 */
class PointHistoryHelper
{
    const HISTORY_MESSAGE_MANUAL_EDIT = 'ポイント手動編集';     // 管理画面会員管理
    const HISTORY_MESSAGE_EDIT = 'ポイント購入完了登録';      //購入フロー完了
    const HISTORY_MESSAGE_ORDER_EDIT = 'ポイント管理画面受注ステータス変更保存';   // 受注編集
    const HISTORY_MESSAGE_ORDER_CANCEL = 'ポイント管理画面受注ステータスキャンセル保存';  // 受注編集

    const HISTORY_MESSAGE_TYPE_CURRENT = '保有';
    const HISTORY_MESSAGE_TYPE_PRE_ADD = '付与(仮)';
    const HISTORY_MESSAGE_TYPE_ADD = '付与(確定)';
    const HISTORY_MESSAGE_TYPE_USE = '利用';
    const HISTORY_MESSAGE_TYPE_ADJUST_USE = '利用調整';

    const STATE_CURRENT = 1;
    const STATE_PRE_ADD = 2;
    const STATE_ADD = 3;
    const STATE_USE = 4;

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
        $this->entities['PointInfo'] = $this->app['eccube.plugin.point.repository.pointinfo']->getLastInsertData();
    }

    /*
    /**
     * 履歴保存に使用するエンティティを新規作成
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
        $entityName = explode('/', get_class($entity));
        $this->entities[array_pop($entityName)] = $entity;

        return;
    }

    /**
     * 保持エンティティを返却
     * @return mixed
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * キーをもとに該当エンティティを削除
     * @param $targetName
     * @return bool
     */
    public function removeEntity($targetName)
    {
        // 指定エンティティの有無を確認し削除
        if (in_array($targetName, $this->entities[$targetName], true)) {
            unset($this->entities[$targetName]);
            return true;
        }

        return false;
    }

    /**
     * 仮ポイント付与情報を履歴登録
     * @param $point
     */
    public function saveProvisionalAddPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_ORDER_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_PRE_ADD;
        $this->historyType = self::STATE_PRE_ADD;
        $this->saveHistoryPoint($point);
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
        $this->app['orm.em']->refresh($this->entities['Point']);
        $this->currentActionName = self::HISTORY_MESSAGE_ORDER_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADD;
        $this->historyType = self::STATE_ADD;
        $this->saveHistoryPoint($point);
    }

    /**
     * 仮ポイント付与情報を履歴登録
     * @param $point
     */
    public function saveShoppingProvisionalAddPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_PRE_ADD;
        $this->historyType = self::STATE_PRE_ADD;
        $this->saveHistoryPoint($point);
    }

    /**
     * 仮ポイント付与情報を確定し履歴登録
     *  - ポイント戻し処理を行ってから今回調整ポイントを付与
     * @param $point
     */
    public function saveShoppingFixProvisionalAddPoint($point)
    {
        // 最終設定ポイント戻し処理
        $this->fixProvisionalAddPoint($point);
        $this->app['orm.em']->refresh($this->entities['Point']);
        $this->currentActionName = self::HISTORY_MESSAGE_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADD;
        $this->historyType = self::STATE_ADD;
        $this->saveHistoryPoint($point);
    }

    /**
     * 最終設定の確定付与ポイントの戻し処理
     * @param $point
     */
    public function fixProvisionalAddPoint($point)
    {
        if (empty($point)) {
            return false;
        }

        $this->currentActionName = self::HISTORY_MESSAGE_ORDER_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_PRE_ADD;
        $this->historyType = self::STATE_PRE_ADD;
        $this->saveHistoryPoint(0 - $point);
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
     * 確定ポイントの打ち消しポイントの保存
     * @param $point
     */
    public function cancelAddPoint($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADD;
        $this->historyType = self::STATE_ADD;
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
    }

    /**
     * 受注画面利用ポイント調整保存
     * @param $point
     */
    public function saveUsePointAdjustOrderHistory($point)
    {
        $this->currentActionName = self::HISTORY_MESSAGE_MANUAL_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_ADJUST_USE;
        $this->historyType = self::STATE_USE;
        $this->saveHistoryPoint($point);
    }

    /**
     * 仮ポイント確定処理
     *  - 最終設定の利用調整ポイントの戻し処理
     * @param $point
     */
    public function fixShoppingProvisionalAddPoint($point)
    {
        if (empty($point)) {
            return false;
        }

        $this->currentActionName = self::HISTORY_MESSAGE_ORDER_EDIT;
        $this->historyActionType = self::HISTORY_MESSAGE_TYPE_PRE_ADD;
        $this->historyType = self::STATE_PRE_ADD;
        $this->saveHistoryPoint(0 - $point);
    }

    /**
     * 履歴登録汎用処理
     * @param $point
     */
    protected function saveHistoryPoint($point)
    {
        if (!$this->hasEntity('Customer')) {
            return false;
        }
        if (!$this->hasEntity('PointInfo')) {
            return false;
        }
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
     *  - 汎用処理
     * @param $point
     */
    public function saveSnapShot($point)
    {
        if (!$this->hasEntity('Customer')) {
            return false;
        }
        $this->entities['SnapShot']->setPlgPointSnapshotId(null);
        $this->entities['SnapShot']->setCustomer($this->entities['Customer']);
        $this->entities['SnapShot']->setPlgPointAdd($point['add']);
        $this->entities['SnapShot']->setPlgPointCurrent($point['current']);
        $this->entities['SnapShot']->setPlgPointUse($point['use']);
        $this->entities['SnapShot']->setPlgPointSnapActionName($this->currentActionName);
        $this->app['orm.em']->persist($this->entities['SnapShot']);
        $this->app['orm.em']->flush($this->entities['SnapShot']);
    }

    /**
     * 引数のエンティティ保持確認
     * @param $name
     * @return bool
     */
    protected function hasEntity($name)
    {
        if (isset($this->entities[$name])) {
            return true;
        }

        return false;
    }
}
