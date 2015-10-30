<?php
namespace Eccube\Paginator;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber;
use Knp\Component\Pager\Event;
use Knp\Component\Pager\PaginatorInterface;

/**
 * Paginator uses event dispatcher to trigger pagination
 * lifecycle events. Subscribers are expected to paginate
 * wanted target and finally it generates pagination view
 * which is only the result of paginator
 * @note ConcreateComponent
 */
class Paginator implements PaginatorInterface
{
    protected $eventDispatcher;
    protected $itemsEvent;
    protected $paginationEvent;
    protected $paginationView;

    private $page;
    private $limit;
    private $options;
    private $item_target;
    private $pagination_target;
    private $pagination_view_total;
    private $pagination_view_items;

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
        if (is_null($this->eventDispatcher)) {
            $this->eventDispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
            $this->eventDispatcher->addSubscriber(new \Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber());
            $this->eventDispatcher->addSubscriber(new \Knp\Component\Pager\Event\Subscriber\Sortable\SortableSubscriber());
        }
        //$this->paginate->eventDispatcher = $eventDispatcher;
    }

    public function getItemsEvent(){
        return $this->itemsEvent;
    }

    public function setItemTarget($target){
        $this->item_target = $target;
    }

    public function setPaginationTarget($target){
        $this->pagination_target = $target;
    }

    public function setPaginationViewItems($items){
        $this->pagination_view_items = $items;
    }

    public function setPaginationViewTotal($total){
        $this->pagination_view_total = $total;
    }

    /**
     * Override the default paginator options
     * to be reused for paginations
     *
     * @param array $options
     */
    public function setDefaultPaginatorOptions(array $options)
    {
        $this->defaultOptions = array_merge($this->defaultOptions, $options);
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
     */
    public function paginate($target, $page = 1, $limit = 10, array $options = array())
    {
        //note. unuse this $target, couse defalentset this.
        $this->limit = intval(abs($limit));
        $this->page = $page;
        if (!$this->limit) {
            throw new \LogicException("Invalid item per page number, must be a positive number");
        }
        $offset = abs($page - 1) * $this->limit;
        $this->options = array_merge($this->defaultOptions, $options);

        // normalize default sort field
        if (isset($this->options['defaultSortFieldName']) && is_array($this->options['defaultSortFieldName'])) {
            $this->options['defaultSortFieldName'] = implode('+', $this->options['defaultSortFieldName']);
        }

        // default sort field and direction are set based on options (if available)
        if (!isset($_GET[$this->options['sortFieldParameterName']]) && isset($this->options['defaultSortFieldName'])) {
            $_GET[$this->options['sortFieldParameterName']] = $options['defaultSortFieldName'];

            if (!isset($_GET[$options['sortDirectionParameterName']])) {
                $_GET[$this->options['sortDirectionParameterName']] = isset($this->options['defaultSortDirection']) ? $this->options['defaultSortDirection'] : 'asc';
            }
        }

        // before pagination start
        $beforeEvent = new \Knp\Component\Pager\Event\BeforeEvent($this->eventDispatcher);
        $this->eventDispatcher->dispatch('knp_pager.before', $beforeEvent);

        // items
        $this->itemsEvent = new \Knp\Component\Pager\Event\ItemsEvent($offset, $limit);
        $this->itemsEvent->options = &$this->options;
        $this->itemsEvent->target = &$this->item_target;
        $this->eventDispatcher->dispatch('knp_pager.items', $this->itemsEvent);
    }

    /**
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function getPaginateView(){
        if (!$this->itemsEvent->isPropagationStopped()) {
            throw new \RuntimeException('One of listeners must count and slice given target');
        }

        // pagination initialization event
        $this->paginationEvent = new \Knp\Component\Pager\Event\PaginationEvent();
        $paginationEvent->target = &$this->pagination_target;
        $this->paginationEvent->options = &$this->options;
        $this->eventDispatcher->dispatch('knp_pager.pagination', $this->paginationEvent);
        if (!$this->paginationEvent->isPropagationStopped()) {
            throw new \RuntimeException('One of listeners must create pagination view');
        }

        // pagination class can be different, with different rendering methods
        $this->paginationView = $this->paginationEvent->getPagination();
        $this->paginationView->setCustomParameters($this->itemsEvent->getCustomPaginationParameters());
        $this->paginationView->setCurrentPageNumber($this->page);
        $this->paginationView->setItemNumberPerPage($this->limit);
        $this->paginationView->setTotalItemCount($this->pagination_view_total);
        $this->paginationView->setPaginatorOptions($this->options);
        $this->paginationView->setItems($this->pagination_view_items);

        // after
        $afterEvent = new \Knp\Component\Pager\Event\AfterEvent($this->paginationView);
        $this->eventDispatcher->dispatch('knp_pager.after', $afterEvent);
        return $this->paginationView;
    }

    /**
     * Hooks in the given event subscriber
     *
     * @param \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber
     */
    public function subscribe(Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        $this->eventDispatcher->addSubscriber($subscriber);
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
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }
}
