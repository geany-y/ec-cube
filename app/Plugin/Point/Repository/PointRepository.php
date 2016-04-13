<?php


namespace Plugin\Point\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
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
        $needStatus[] = PointHistoryHelper::STATE_ADD;      // 加算ポイント
        $needStatus[] = PointHistoryHelper::STATE_CURRENT;  // 手動ポイント
        $needStatus[] = PointHistoryHelper::STATE_USE;      // 利用ポイント

        try {
            $orderStatus = new OrderStatus();
            $orderStatus->setId(8);

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
     * @param Order $order
     * @return array|bool|null
     */
    /*
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
    */

    /**
     * 仮ポイントを会員IDを基に返却
     *  - 合計値
     * @param $customer_id
     * @return array|bool|null
     */
    /*
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
    */

    /**
     * 仮確定ポイントレコードを受注情報を基に返却
     * @param $order
     * @return array|bool|null
     */
    /*
    public function getFixProvisionalAddPointByOrder($order)
    {
        // 必要エンティティ判定
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
    */

    /**
     * 仮ポイントレコードを受注情報を基に返却
     * @param $order
     * @return array|bool|null
     */
    public function getProvisionalAddPointByOrder($order)
    {
        // 必要エンティティ判定
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
    /*
    public function getLastProvisionalAddPointByOrder($order)
    {
        // 必要エンティティ判定
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
    */

    /**
     * 仮ポイントを受注情報をもとに取得
     *  -   一番最後に設定された値を取得
     * @param $order
     * @return array|bool|null
     */
    public function getLastAddPointByOrder($order)
    {
        // 必要エンティティ判定
        if (empty($order)) {
            return false;
        }

        // 必要ステータス
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
        $customer = $order->getCustomer();
        if (empty($customer)) {
            return null;
        }
        try {
            //$orderStatus = new OrderStatus();
            //$orderStatus->setId(8);
            // 履歴情報をもとに現在利用ポイントを計算し取得
            $qb = $this->createQueryBuilder('p')
                //->addSelect('o')
                ->where('p.customer_id = :customerId')
                ->andwhere('p.order_id = :orderId')
                ->andwhere('p.plg_point_type = :pointType')
                ->setParameter('customerId', $order->getCustomer()->getId())
                ->setParameter('orderId', $order->getId())
                ->setParameter('pointType', PointHistoryHelper::STATE_USE)
                ->orderBy('p.plg_point_id', 'desc')
                //->leftJoin('p.Order', 'o')
                //->andwhere('o.OrderStatus != :orderStatus')
                //->setParameter('orderStatus', $orderStatus)
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
     * 最終仮利用ポイントを取得
     * @param Order $order
     * @return int|null|number
     */
    public function getLastPreUsePoint(Order $order)
    {
        try {
            // 履歴情報をもとに現在利用ポイントを計算し取得
            $qb = $this->createQueryBuilder('p')
                //->addSelect('o')
                ->where('p.customer_id = :customerId')
                ->andwhere('p.order_id = :orderId')
                ->andwhere('p.plg_point_type = :pointType')
                ->setParameter('customerId', $order->getCustomer()->getId())
                ->setParameter('orderId', $order->getId())
                ->setParameter('pointType', PointHistoryHelper::STATE_PRE_USE)
                ->orderBy('p.plg_point_id', 'desc')
                //->leftJoin('p.Order', 'o')
                //->andwhere('o.OrderStatus != :orderStatus')
                //->setParameter('orderStatus', $orderStatus)
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
        // 必要エンティティ判定
        if (empty($order)) {
            return false;
        }

        // 必要ステータス設定
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
    /**
     * protected function getLastSetManualId($customer_id)
     * {
     * try {
     * // 履歴情報をもとに現在ポイントを計算し取得
     * $qb = $this->createQueryBuilder('p')
     * ->where('p.customer_id = :customer_id')
     * ->andWhere('p.plg_point_type = :pointType')
     * ->setParameter('customer_id', $customer_id)
     * ->setParameter('pointType', PointHistoryHelper::STATE_CURRENT)
     * ->orderBy('p.create_date', 'desc')
     * ->setMaxResults(1);
     * $max_manual_point = $qb->getQuery()->getOneOrNullResult();
     *
     * // 取得値判定
     * if (is_null($max_manual_point)) {
     * return false;
     * }
     *
     * return $max_manual_point->getPlgPointId();
     * } catch (NoResultException $e) {
     * return null;
     * }
     * }*/
}
