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

use Doctrine\DBAL\Exception\DatabaseObjectNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Plugin\Point\Entity\PointCustomer;

/**
 * PointCustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PointCustomerRepository extends EntityRepository
{
    /**
     * 保有ポイントの保存
     * @param $point
     * @param $customer
     * @return PointCustomer
     * @throws DatabaseObjectNotFoundException
     */
    public function savePoint($point, $customer)
    {
        // エンティティにフォーム取得値とリレーションオブジェクトを設定
        $pointCustomerEntity = new PointCustomer();
        $pointCustomerEntity->setPlgPointCurrent($point);
        $pointCustomerEntity->setCustomer($customer);

        try {

            // DB更新
            $em = $this->getEntityManager();
            $em->persist($pointCustomerEntity);
            $em->flush($pointCustomerEntity);

            return $pointCustomerEntity;
        } catch (DatabaseObjectNotFoundException $e) {
            throw new DatabaseObjectNotFoundException();
        }
    }

    /**
     * 前回保存のポイントと今回保存のポイントの値を判定
     * @param $point
     * @param $customerId
     * @return bool
     * @throws NotFoundHttpException
     */
    public function isSamePoint($point, $customerId)
    {
        // 最終設定値を会員IDから取得
        $lastPoint = $this->getLastPointById($customerId);

        // 値が同じ場合
        if ((integer)$point === (integer)$lastPoint) {
            return true;
        }

        return false;
    }

    /**
     * 会員IDをもとに一番最後に保存した保有ポイントを取得
     * @param $customerId
     * @return null
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastPointById($customerId)
    {
        // 値が取得出来ない際は処理をキャンセル
        if (empty($customerId)) {
            return null;
        }

        try {
            // 会員IDをもとに最終保存の保有ポイントを取得
            $qb = $this->createLastPointBaseQuery();
            $qb->where('pc.customer_id = :customerId')
                ->setParameter('customerId', $customerId)
                ->orderBy('pc.create_date', 'desc')
                ->setMaxResults(1);

            $result = $qb->getQuery()->getOneOrNullResult();

            if(is_null($result)){
                return null;
            }

            return $result->getPlgPointCurrent();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * 最終データ取得時の共通QueryBuilder作成
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createLastPointBaseQuery()
    {
        // 最終データ取得共通クエリビルダーを作成
        return $this->createQueryBuilder('pc')
            ->orderBy('pc.update_date', 'DESC');
    }
}
