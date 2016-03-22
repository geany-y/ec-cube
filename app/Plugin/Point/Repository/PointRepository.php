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


namespace Plugin\Point\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Eccube\Entity\Order;
use Plugin\Point\Entity\Point;
use Plugin\Point\Entity\PointInfo;
use Plugin\Point\Resource\lib\PointHistoryHelper\PointHistoryHelper;

/**
 * Class PointRepository
 * @package Plugin\Point\Repository
 */
class PointRepository extends EntityRepository
{
    /**
     * カスタマーIDを基準にポイントの合計を計算
     * @param $customerId
     * @return bool|null
     */
    public function getCalculateCurrentPointByCustomerId($customerId)
    {
        $needStatus = array();
        $needStatus[] = PointHistoryHelper::STATE_ADD;
        $needStatus[] = PointHistoryHelper::STATE_CURRENT;
        $needStatus[] = PointHistoryHelper::STATE_USE;


        try {
            $qb = $this->createQueryBuilder('p');
            $qb->addSelect('SUM(p.plg_dynamic_point) as point_sum')
                    ->add('where', $qb->expr()->in('p.plg_point_type', $needStatus))
                    ->andWhere('p.customer_id = :customer_id')
                    ->setParameter('customer_id', $customerId);

            $sum_point = $qb->getQuery()->getResult();

            // 情報が取得できない場合
            if (count($sum_point) < 1) {
                return false;
            }

            // 値がマイナスになった場合@todo 将来拡張
            if ($sum_point[0]['point_sum'] < 0) {
                return false;
            }

            return $sum_point[0]['point_sum'];
        }catch(NoResultException $e){
            throw new NoResultException();
            return null;
        }
    }

    /**
     * 最後に調整したポイントをカスタマーID・最終手動更新pl_point_idをもとに取得
     * @param $manualId
     * @param $customerId
     * @return int
     * @throws NoResultException
     */
    /*
    public function getLastManualUsePoint($manualId, $customerId){
        try{
            $qb = $this->createQueryBuilder('p');
            if (!empty($manualId)) {
                // 手動あり
                $qb->addSelect('SUM(p.plg_dynamic_point) as point_sum')
                ->addSelect('MAX(p.create_date) as max_date')
                ->where('p.customer_id = :customerId')
                ->groupBy('p.order_id')
                ->groupBy('p.create_date')
                ->andHaving('p.plg_point_type = :pointType')
                ->andHaving('p.plg_point_id >= :plgPointId')
                ->setParameter('plgPointId', $manualId)
                ->setParameter('customerId', $customerId)
                ->setParameter('pointType', PointHistoryHelper::STATE_ADJUST_USE);
            } else {
                // 手動なし
                $qb->addSelect('SUM(p.plg_dynamic_point) as point_sum')
                    ->addSelect('MAX(p.create_date) as max_date')
                    ->andwhere('p.plg_point_type = :pointType')
                    ->groupBy('p.order_id')
                    ->groupBy('p.create_date')
                    ->setParameter('pointType', PointHistoryHelper::STATE_ADJUST_USE);
            }

            $sum_use = $qb->getQuery()->getResult();



            // 情報が取得できない場合
            if (count($sum_use) < 1) {
                return 0;
            }

            // 値がマイナスになった場合@todo 将来拡張
            if ($sum_use[0]['point_sum'] < 0) {
                return 0;
            }

            return $sum_use[0]['point_sum'];
        }catch(NoResultException $e){
            throw new NoResultException;
        }
    }
    */

    /**
     * 仮ポイントをオーダーエンティティを基に返却
     *  - 仮ポイントの合計
     */
    public function getAllProvisionalAddPointByOrder(Order $order)
    {
        try {
            $qb = $this->createQueryBuilder('p')
                ->addSelect('SUM(p.plg_dynamic_point) as point_sum')
                ->where('p.order_id = :orderId')
                ->andWhere('p.customer_id = :customerId')
                ->andWhere('p.plg_point_type = :pointType')
                ->setParameter('orderId', $order->getId())
                ->setParameter('customerId', $order->getCustomer()->getId())
                ->setParameter('pointType', PointHistoryHelper::STATE_PRE_ADD);
            $provisionalSum = $qb->getQuery()->getResult();

            if (count($provisionalSum) < 1) {
                return false;
            }

            $provisionalSum = $provisionalSum[0]['point_sum'] * -1;

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($provisionalSum < 0) {
                return false;
            }

            return $provisionalSum;
        }catch(NoResultException $e){
            return null;
        }
    }

    /**
     * 仮ポイントを会員IDを基に返却
     *  -
     * @param $customer_id
     * @return bool|mixed
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAllProvisionalAddPoint($customer_id)
    {
        //$lastManualId = $this->getLastSetManualId($customer_id);

        try {
            $qb = $this->createQueryBuilder('p')
                ->addSelect('SUM(p.plg_dynamic_point) as point_sum')
                ->andWhere('p.plg_point_type = :pointType')
                ->andWhere('p.customer_id = :customer_id')
                ->setParameter('pointType', PointHistoryHelper::STATE_PRE_ADD)
                ->setParameter('customer_id', $customer_id);

            $provisionalAddPoint = $qb->getQuery()->getResult();


            // 仮ポイント取得判定
            if (count($provisionalAddPoint) < 1) {
                return false;
            }

            $provisionalAddPoint = $provisionalAddPoint[0]['point_sum'] * -1;

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($provisionalAddPoint < 0) {
                return false;
            }

            return $provisionalAddPoint;
        }catch(NoResultException $e){
            return null;
        }
    }

    /**
     * 最後に使用したポイントを受注エンティティより取得
     * @param Order $order
     * @return bool|number
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastAdjustUsePoint(Order $order)
    {
        //try {
            // 履歴情報をもとに現在ポイントを計算し取得
            $qb = $this->createQueryBuilder('p')
                ->where('p.customer_id = :customerId')
                ->andwhere('p.order_id = :orderId')
                ->andwhere('p.plg_point_type = :pointType')
                ->setParameter('customerId', $order->getCustomer()->getId())
                ->setParameter('orderId', $order->getId())
                ->setParameter('pointType', PointHistoryHelper::STATE_USE)
                ->orderBy('p.create_date', 'desc')
                ->setMaxResults(1);
            $max_use_point = $qb->getQuery()->getOneOrNullResult();

            // 取得値判定
            if (!is_null($max_use_point)) {
                return abs($max_use_point->getPlgDynamicPoint());
            }

            // 履歴情報をもとに現在ポイントを計算し取得
            //$qb->setParameter('pointType', PointHistoryHelper::STATE_USE);
            //$max_use_point = $qb->getQuery()->getOneOrNullResult();

            // 取得値判定
            /*
            if (is_null($max_use_point)) {
                return false;
            }
            */

            return abs($max_use_point->getPlgDynamicPoint());
        //}catch(NoResultException $e){
            //return null;
        //}
    }

    /**
     * 最終設定の手動ポイントを取得
     * @param $customer_id
     * @return bool
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getLastSetManualId($customer_id)
    {
        try {
            // 履歴情報をもとに現在ポイントを計算し取得
            $qb = $this->createQueryBuilder('p')
                ->where('p.customer_id = :customer_id')
                ->andWhere('p.plg_point_type = :pointType')
                ->setParameter('customer_id', $customer_id)
                ->setParameter('pointType', PointHistoryHelper::STATE_CURRENT)
                ->orderBy('p.create_date', 'desc')
                ->setMaxResults(1);
            $max_manual_point = $qb->getQuery()->getOneOrNullResult();

            // 取得値判定
            if (is_null($max_manual_point)) {
                return false;
            }

            return $max_manual_point->getPlgPointId();
        }catch(NoResultException $e){
            return null;
        }
    }
}
