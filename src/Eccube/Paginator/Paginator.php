<?php

namespace Eccube\Paginator;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber;
use Knp\Component\Pager\Event;
use Knp\Component\Pager\PaginatorInterface;
use Eccube\Paginator\DefaultPaginator;

/**
 * Paginator uses event dispatcher to trigger pagination
 * lifecycle events. Subscribers are expected to paginate
 * wanted target and finally it generates pagination view
 * which is only the result of paginator
 */
class Paginator implements PaginatorInterface
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Knp\Component\Pager\PaginatorInterface
     */
    protected $paginator;

    /**
     * Default options of paginator
     *
     * @var array
     */
    protected $defaultOptions = array(
        'pageParameterName' => 'page',
        'sortFieldParameterName' => 'sort',
        'sortDirectionParameterName' => 'direction',
        'filterFieldParameterName' => 'filterParam',
        'filterValueParameterName' => 'filterValue',
        'distinct' => true
    );

    /**
     * Initialize paginator with event dispatcher
     * Can be a service in concept. By default it
     * hooks standard pagination subscriber
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher = null)
    {
        if(empty($this->paginator)){
            $this->paginator = new \Eccube\Paginator\DefaultPaginator();
        }
        $this->_attributeDispatcher($eventDispatcher);
        return false;
    }

    /**
     * Initialize paginator with event dispatcher
     * Can be a service in concept. By default it
     * hooks standard pagination subscriber
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    protected function _attributeDispatcher(Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher = null)
    {
        if (is_null($this->paginator->eventDispatcher)) {
            $this->paginator->eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
            $this->paginator->eventDispatcher->addSubscriber(new \Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber());
            $this->paginator->eventDispatcher->addSubscriber(new \Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber());
            return true;
        }
        $this->paginate->eventDispatcher = $eventDispatcher;
    }

    /**
     * Set Decorator Concreate
     *
     * @param \Knp\Component\Pager\Pagination\PaginationInterface $pagination
     * @return boolean
     */
    public function setCustomPagination(Paginator $pagination = null)
    {
        if(!empty($pagination)){
            $this->pagination = $pagination;
            $this->_attributeDispatcher();
            return $pagination;
        }
        //return false;
    }

    /**
     * Override the default paginator options
     * to be reused for paginations
     *
     * @param array $options
     */
    public function setDefaultPaginatorOptions(array $options)
    {
        $this->paginator->defaultOptions = array_merge($this->defaultOptions, $options);
    }

    /**
     * Paginates anything (depending on event listeners)
     * into Pagination object, which is a view targeted
     * pagination object (might be aggregated helper object)
     * responsible for the pagination result representation
     *
     * @param mixed $target - anything what needs to be paginated
     * @param integer $page - page number, starting from 1
     * @param integer $limit - number of items per page
     * @param array $options - less used options:
     *     boolean $distinct - default true for distinction of results
     *     string $alias - pagination alias, default none
     *     array $whitelist - sortable whitelist for target fields being paginated
     * @throws \LogicException
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        var_dump('HERE');
        exit();
        return $this->paginator->paginate($target, $page, $limit, $options);
    }

    /**
     * Hooks in the given event subscriber
     *
     * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
     */
    public function subscribe(Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        $this->paginator->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * Hooks the listener to the given event name
     *
     * @param string $eventName
     * @param object $listener
     * @param integer $priority
     */
    public function connect($eventName, $listener, $priority = 0)
    {
        $this->paginator->eventDispatcher->addListener($eventName, $listener, $priority);
    }
}
