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
namespace Eccube\Paginator;

use Knp\Component\Pager\Event;

/**
 *  @class ConcreateDecorator
 *  @param \Knp\Component\Pager\PaginatorInterface
 *  @return \Knp\Component\Pager\PaginatorInterface
 *
 */
class AssociationPaginator extends AbstaractPagnator
{
    private $deleteDQLPartNames;
    private $mainTableAlias;

    public function __construct(\Knp\Component\Pager\PaginatorInterface $paginator)
    {
        parent::__construct($paginator);
        $this->deleteParameter = array();
    }

    /**
     * @return bollean
     */
    public function setMainTableAlias($mainTableAlias = null)
    {
        if(empty($mainTableAlias)){
            return false;
        }
        $this->mainTableAlias = $mainTableAlias;
        return true;
    }

    /**
     * @return bollean
     */
    public function setDeleteDQLParts(array $deleteDQLPartNames = null)
    {
        if(empty($deleteDQLPartNames)){
            return false;
        }
        $this->deleteDQLPartNames = $deleteDQLPartNames;
        return true;
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        //リミット・オフセット計算
        $this->limit = intval(abs($limit));
        $this->page = $page;
        if (!$this->limit) {
            throw new \LogicException('Invalid item per page number, must be a positive number');
        }
        $offset = abs($page - 1) * $this->limit;

        //トータル件数を取得
        $counter = clone $target;
        if (is_array($this->deleteDQLPartNames) && count($this->deleteDQLPartNames) > 0) {
            $max = count($this->deleteDQLPartNames);
            for ($i = 0; $i < $max; $i++) {
                $counter->resetDQLpart($this->deleteDQLPartNames[$i]);
            }
        }
        $counter->select('count('.$this->mainTableAlias.')');
        $total = count($target->getQuery()->getScalarResult());

        //クエリビルダーにリミット・オフセット付与
        $target->setFirstResult($offset)
               ->setMaxResults($limit);

        //検索結果取得
        $queryRes = $target->getQuery()->getResult();
        $items = array();

        //不要なセレクト句を排除
        foreach($queryRes as $res){
            $items[] = $res[0];
        }

        parent::setItemTarget($items);                          //アイテムイベントに取得配列を受け渡す
        parent::setPaginationTarget($items);                    //ページネーターイベントに取得配列を受け渡す
        parent::paginate($items, $page, $limit, $options);      //既存のページネート処理
        parent::setPaginationViewItems($items);                 //ページネートビューイベントに取得配列を受け渡す
        parent::setPaginationViewTotal($total);                 //取得情報のトータルを設定

        return parent::getPaginateView();
    }
}
