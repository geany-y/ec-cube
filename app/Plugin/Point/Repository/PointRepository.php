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
use Plugin\Point\Entity\PointInfo;
use Plugin\Point\Helper\PointHistoryHelper\PointHistoryHelper;

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
        // ログテーブルから抽出するステータス
        $needStatus = array();
        $needStatus[] = PointHistoryHelper::STATE_ADD;      // 付与ポイント
        $needStatus[] = PointHistoryHelper::STATE_CURRENT;  // 現在ポイント
        $needStatus[] = PointHistoryHelper::STATE_USE;      // 利用ポイント


        try {
            // ログテーブルからポイントを計算
            $qb = $this->createQueryBuilder('p');
            $qb->addSelect('SUM(p.plg_dynamic_point) as point_sum')
                ->add('where', $qb->expr()->in('p.plg_point_type', $needStatus))
                ->andWhere('p.customer_id = :customer_id')
                ->setParameter('customer_id', $customerId);

            // 合計ポイント
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
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 仮ポイントをオーダーエンティティを基に返却
     *  - 仮ポイントの合計
     * @param Order $order
     * @return array|bool|null
     */
    public function getAllProvisionalAddPointByOrder(Order $order)
    {
        try {
            // 受注情報をもとに仮付与ポイントを合計
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

            $provisionalSum = $provisionalSum[0]['point_sum'];

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($provisionalSum < 0) {
                return false;
            }

            return $provisionalSum;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 仮ポイントを会員IDを基に返却
     *  - 合計値
     * @param $customer_id
     * @return array|bool|null
     */
    public function getAllProvisionalAddPoint($customer_id)
    {
        try {
            // 会員IDをもとに仮付与ポイントを計算
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

            $provisionalAddPoint = $provisionalAddPoint[0]['point_sum'];

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($provisionalAddPoint < 0) {
                return false;
            }

            return $provisionalAddPoint;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 仮確定ポイントレコードを受注情報を基に返却
     * @param $order
     * @return array|bool|null
     */
    public function getFixProvisionalAddPointByOrder($order)
    {
        if (empty($order)) {
            return false;
        }

        try {
            // 受注をもとに仮付与ポイントを計算
            $qb = $this->createQueryBuilder('p')
                ->andWhere('p.plg_point_type = :pointType')
                ->andWhere('p.customer_id = :customer_id')
                ->andWhere('p.order_id = :order_id')
                ->setParameter('pointType', PointHistoryHelper::STATE_ADD)
                ->setParameter('customer_id', $order->getCustomer()->getId())
                ->setParameter('order_id', $order->getId())
                ->orderBy('p.plg_point_id', 'desc')
                ->setMaxResults(1);

            $provisionalAddPoint = $qb->getQuery()->getResult();

            // 仮ポイント取得判定
            if (count($provisionalAddPoint) < 1) {
                return false;
            }

            $provisionalAddPoint = $provisionalAddPoint[0]->getPlgDynamicPoint();

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($provisionalAddPoint < 0) {
                return false;
            }

            return $provisionalAddPoint;
        } catch (NoResultException $e) {
            return null;
        }
    }


    /**
     * 仮ポイントレコードを受注情報を基に返却
     * @param $order
     * @return array|bool|null
     */
    public function getProvisionalAddPointByOrder($order)
    {
        if (empty($order)) {
            return false;
        }

        try {
            // 受注をもとに仮付与ポイントを計算
            $qb = $this->createQueryBuilder('p')
                ->andWhere('p.plg_point_type = :pointType')
                ->andWhere('p.customer_id = :customer_id')
                ->andWhere('p.order_id = :order_id')
                ->setParameter('pointType', PointHistoryHelper::STATE_PRE_ADD)
                ->setParameter('customer_id', $order->getCustomer()->getId())
                ->setParameter('order_id', $order->getId())
                ->orderBy('p.plg_point_id', 'desc')
                ->setMaxResults(1);

            $provisionalAddPoint = $qb->getQuery()->getResult();

            // 仮ポイント取得判定
            if (count($provisionalAddPoint) < 1) {
                return false;
            }

            $provisionalAddPoint = $provisionalAddPoint[0]->getPlgDynamicPoint();

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($provisionalAddPoint < 0) {
                return false;
            }

            return $provisionalAddPoint;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 仮ポイントレコードを受注情報をもとに取得
     *  -   一番最後に設定された値を取得
     * @param $order
     * @return array|bool|null
     */
    public function getLastProvisionalAddPointByOrder($order)
    {
        if (empty($order)) {
            return false;
        }

        try {
            // 会員IDをもとに仮付与ポイントを計算
            $qb = $this->createQueryBuilder('p')
                ->andWhere('p.plg_point_type = :pointType')
                ->andWhere('p.customer_id = :customer_id')
                ->andWhere('p.order_id = :order_id')
                ->orderBy('p.create_date', 'desc')
                ->setParameter('pointType', PointHistoryHelper::STATE_PRE_ADD)
                ->setParameter('customer_id', $order->getCustomer()->getId())
                ->setParameter('order_id', $order->getId())
                ->setMaxResults(1);

            $provisionalAddPoint = $qb->getQuery()->getResult();

            // 仮ポイント取得判定
            if (count($provisionalAddPoint) < 1) {
                return false;
            }


            //$provisionalAddPoint = $provisionalAddPoint[0]->getPlgDynamicPoint() * -1;
            $provisionalAddPoint = $provisionalAddPoint[0]->getPlgDynamicPoint();

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($provisionalAddPoint < 0) {
                return false;
            }

            return $provisionalAddPoint;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 仮ポイントを受注情報をもとに取得
     *  -   一番最後に設定された値を取得
     * @param $order
     * @return array|bool|null
     */
    public function getLastAddPointByOrder($order)
    {
        if (empty($order)) {
            return false;
        }

        $needStatus = array();
        $needStatus[] = PointHistoryHelper::STATE_ADD;
        $needStatus[] = PointHistoryHelper::STATE_PRE_ADD;

        try {
            // 会員IDをもとに仮付与ポイントを計算
            $qb = $this->createQueryBuilder('p');
            $qb->add('where', $qb->expr()->in('p.plg_point_type', $needStatus))
                ->andWhere('p.customer_id = :customer_id')
                ->andWhere('p.order_id = :order_id')
                ->orderBy('p.create_date', 'desc')
                ->setParameter('customer_id', $order->getCustomer()->getId())
                ->setParameter('order_id', $order->getId())
                ->setMaxResults(1);

            $lastAddPoint = $qb->getQuery()->getResult();

            // 仮ポイント取得判定
            if (count($lastAddPoint) < 1) {
                return false;
            }


            $lastAddPoint = $lastAddPoint[0]->getPlgDynamicPoint();

            // 仮ポイントがマイナスになった場合はエラー表示
            if ($lastAddPoint < 0) {
                return false;
            }

            return $lastAddPoint;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 最終利用ポイントを受注エンティティより取得
     * @param Order $order
     * @return int|null|number
     */
    public function getLastAdjustUsePoint(Order $order)
    {
        try {
            // 履歴情報をもとに現在利用ポイントを計算し取得
            $qb = $this->createQueryBuilder('p')
                ->where('p.customer_id = :customerId')
                ->andwhere('p.order_id = :orderId')
                ->andwhere('p.plg_point_type = :pointType')
                ->setParameter('customerId', $order->getCustomer()->getId())
                ->setParameter('orderId', $order->getId())
                ->setParameter('pointType', PointHistoryHelper::STATE_USE)
                ->orderBy('p.plg_point_id', 'desc')
                ->setMaxResults(1);
            $max_use_point = $qb->getQuery()->getResult();

            // 取得値判定
            if (count($max_use_point) < 1) {
                return 0;
            }

            return abs($max_use_point[0]->getPlgDynamicPoint());
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 受注情報をもとに、最終保存の仮ポイントが確定かどうか判定
     * @param $order
     * @return bool|null
     */
    public function isLastProvisionalFix($order)
    {
        if (empty($order)) {
            return false;
        }

        $needStatus = array();
        $needStatus[] = PointHistoryHelper::STATE_PRE_ADD;
        $needStatus[] = PointHistoryHelper::STATE_ADD;

        try {
            // 受注をもとに仮付与ポイントを計算
            $qb = $this->createQueryBuilder('p');
            $qb->add('where', $qb->expr()->in('p.plg_point_type', $needStatus))
                ->andWhere('p.customer_id = :customer_id')
                ->andWhere('p.order_id = :order_id')
                ->setParameter('customer_id', $order->getCustomer()->getId())
                ->setParameter('order_id', $order->getId())
                ->orderBy('p.plg_point_id', 'desc')
                ->setMaxResults(1);

            $provisionalAddPoint = $qb->getQuery()->getResult();

            // 仮ポイント取得判定
            if (count($provisionalAddPoint) < 1) {
                return false;
            }

            $pointType = $provisionalAddPoint[0]->getPlgPointType();

            if ($pointType == PointHistoryHelper::STATE_ADD) {
                return true;
            }

            return false;
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 最終設定の手動ポイントを取得
     * @param $customer_id
     * @return bool|null
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
        } catch (NoResultException $e) {
            return null;
        }
    }
}
