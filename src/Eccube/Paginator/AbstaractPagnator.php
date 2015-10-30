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
use Knp\Component\Pager\PaginatorInterface;

/**
 *  @note Decorator
 *  @param \Knp\Component\Pager\PaginatorInterface
 *  @return \Knp\Component\Pager\PaginatorInterface
 *
 */
abstract class AbstaractPagnator implements PaginatorInterface
{
    protected $paginator;

    /**
     * @param \Knp\Component\Pager\PaginatorInterface
     */
    public function __construct(\Knp\Component\Pager\PaginatorInterface $paginator = null)
    {
        if(empty($paginator)){
            throw new \RuntimeException('Not given paginator object,this class constructor must be need object.');
        }
        $this->paginator = $paginator;
    }

    public function getPaginator()
    {
        return $this->paginator;
    }

    public function setItemTarget($target)
    {
        $this->paginator->setItemTarget($target);
    }

    public function setPaginationTarget($target)
    {
        $this->paginator->setPaginationTarget($target);
    }

    public function setPaginationViewItems($items)
    {
        $this->paginator->setPaginationViewItems($items);
    }

    public function setPaginationViewTotal($total)
    {
        $this->paginator->setPaginationViewTotal($total);
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        $this->paginator->paginate($target, $page, $limit, $options);
    }

    public function getPaginateView()
    {
        return $this->paginator->getPaginateView();
    }
}
