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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Plugin\Point\Entity\PointInfoAddStatus;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PointInfoRepository
 * @package Plugin\Point\Repository
 */
class PointInfoRepository extends EntityRepository
{
    /** @var \Eccube\Application */
    protected $app;

    /**
     * PointInfoRepository constructor.
     * @param EntityManager $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManager $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->app = \Eccube\Application::getInstance();
    }

    /**
     * ポイント基本情報を保存
     *  - 受注ステータス・ユーザー設定不可項目をダミーとして追加
     * @param \Plugin\Point\Entity\PointInfo $pointInfo
     * @return bool
     * @throws NoResultException
     */
    public function save(\Plugin\Point\Entity\PointInfo $pointInfo)
    {
        try {
            //保存処理(登録)
            $em = $this->getEntityManager();
            $em->persist($pointInfo);
            $em->flush();

            return true;
        } catch (NoResultException $e) {
            throw new NoResultException();
        }
    }

    /**
     * ポイント機能基本設定情報で最後に設定した内容を取得
     * @return null
     * @throws NoResultException
     */
    public function getLastInsertData()
    {
        try {
            // アソシエーションデータを含む最終データ取得のために親データの最終IDを取得
            $qb = $this->createQueryBuilder('pi')
                ->orderBy('pi.create_date', 'DESC')
                ->setMaxResults(1);


            $result = $qb->getQuery()->getOneOrNullResult();

            // エラー判定
            if (is_null($result)) {
                return null;
            }

            return $result;
        } catch (NoResultException $e) {
            return null;
        }
    }
}
