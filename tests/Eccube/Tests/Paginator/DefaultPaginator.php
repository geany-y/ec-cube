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

use Knp\Component\Pager\PaginatorInterface;

/**
 *  @class ConcreateDecorator
 *  @param \Knp\Component\Pager\PaginatorInterface
 *  @return \Knp\Component\Pager\PaginatorInterface
 *
 */
class DefaultPaginator extends AbstaractPagnator
{

    public function __construct(\Knp\Component\Pager\PaginatorInterface $paginator)
    {
        parent::__construct($paginator);
    }

    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        parent::setItemTarget($target);
        parent::setPaginationTarget($target);
        parent::paginate($target, $page, $limit, $options);
        parent::setPaginationViewItems(parent::getPaginator()->getItemsEvent()->items);
        parent::setPaginationViewTotal(parent::getPaginator()->getItemsEvent()->count);
        return parent::getPaginateView();
    }
}
