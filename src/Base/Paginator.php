<?php
namespace Base;

abstract class Paginator extends Logic\AbstractLogic implements Paginator\PaginatorInterface
{
    protected $modelName;
    
    protected $filterFormName;
    
    protected $headers = [];
    
    protected $currentPage = 1;
    
    protected $itemsPerPage = 10;
    
    protected $pagesRange = 5;
    
    protected $select;
    
    protected $isInitialized = false;
    
    protected $isResponsive = true;
    
    protected $pageLoadTimeMs;
    
    protected $noResultsText = '<div class="card"><div class="card-body">Brak wyników</div></div>';
    
    protected $perPageOptions = [
        '10',
        '20',
        '50',
        '100',
    ];
    
    protected $ajaxUrl;
    
    protected $paginatorId;
    
    protected $arrayObjectPrototype;
    
    protected $sortingOptions = [];
    
    protected $defaultSorting = [];
    
    protected $tableActionsPartial;
    
    public function init()
    {
        $this->initSelect();
        
        $this->setIsInitialized(true);
    }
    
    public function getFilterFormName()
    {
        return $this->filterFormName;
    }

    public function setFilterFormName($filterFormName)
    {
        $this->filterFormName = $filterFormName;
    }
    
    public function getModelName()
    {
        return $this->modelName;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function getItemsPerPage()
    {
        $container = $this->getStorageContainer();
        $itemsPerPage = $container->itemsPerPage;
        
        if (empty($itemsPerPage)) {
            $itemsPerPage = $this->itemsPerPage;
        }
        
        return $itemsPerPage;
    }
    
    public function getIsResponsive()
    {
        return $this->isResponsive;
    }

    public function setIsResponsive($isResponsive)
    {
        $this->isResponsive = $isResponsive;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function setCurrentPage($currentPage)
    {
        $totalPages = $this->getTotalPages();
        
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        
        $this->currentPage = $currentPage;
    }

    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }
    
    /**
     * @return \Laminas\Db\Sql\Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    public function setSelect($select)
    {
        $this->select = $select;
    }
    
    public function getPagesRange()
    {
        return $this->pagesRange;
    }

    public function setPagesRange($pagesRange)
    {
        $this->pagesRange = $pagesRange;
    }
    
    public function getPageLoadTimeMs()
    {
        return $this->pageLoadTimeMs;
    }

    public function setPageLoadTimeMs($pageLoadTimeMs)
    {
        $this->pageLoadTimeMs = $pageLoadTimeMs;
    }
    
    public function getIsInitialized()
    {
        return $this->isInitialized;
    }
    
    public function getNoResultsText()
    {
        return $this->noResultsText;
    }
    
    public function setNoResultsText($noResultsText)
    {
        $this->noResultsText = $noResultsText;
    }
    
    public function getPerPageOptions()
    {
        return $this->perPageOptions;
    }

    public function setPerPageOptions(array $perPageOptions)
    {
        $this->perPageOptions = $perPageOptions;
    }
    
    public function getAjaxUrl()
    {
        return $this->ajaxUrl;
    }

    public function setAjaxUrl($ajaxUrl)
    {
        $this->ajaxUrl = $ajaxUrl;
    }
    
    public function getPaginatorId()
    {
        $paginatorId =  $this->paginatorId;
        
        if (empty($paginatorId)) {
            $paginatorId = str_replace(['\\'], ['_'], get_class($this));
        }
        
        return $paginatorId;
    }

    public function setPaginatorId($paginatorId)
    {
        $this->paginatorId = $paginatorId;
    }

    public function getArrayObjectPrototype()
    {
        return $this->arrayObjectPrototype;
    }

    /**
     * Ustaw prototyp, z którego będą pobierane dane kolumn oraz akcje kolumn dla modelu
     * @param type $arrayObjectPrototype
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        $this->arrayObjectPrototype = $arrayObjectPrototype;
    }
    
    /**
     * Pobierz tablicę zawierającą dostępne opcje sortowania
     * @return array
     */
    public function getSortingOptions()
    {
        return $this->sortingOptions;
    }

    public function setSortingOptions($sortingOptions): void
    {
        $this->sortingOptions = $sortingOptions;
    }
    
    public function getDefaultSorting()
    {
        return $this->defaultSorting;
    }

    public function setDefaultSorting(array $defaultSorting)
    {
        $this->defaultSorting = $defaultSorting;
    }

    public function getTableActionsPartial()
    {
        return $this->tableActionsPartial;
    }

    public function setTableActionsPartial($tableActionsPartial): void
    {
        $this->tableActionsPartial = $tableActionsPartial;
    }
     
    /**
     * Pobierz liczbę stron paginatora
     * @return integer
     * @throws \Exception
     */
    public function getTotalPages()
    {
        $itemsPerPage = $this->getItemsPerPage();
        $totalResults = $this->getTotalResults();
        
        return ceil($totalResults / $itemsPerPage);
    }
    
    /**
     * Pobierz pierwszą stronę w wyznaczonym zakresie
     * @return int
     */
    public function getFirstPageInRange()
    {
        $currentPage = $this->getCurrentPage();
        $totalPages = $this->getTotalPages();
        $pagesRange = $this->getPagesRange();
        
        $firstPage = $currentPage - (floor($pagesRange / 2));
        
        if ($totalPages - $firstPage < $pagesRange) {
            $firstPage = $totalPages - $pagesRange + 1;
        }
        
        if ($firstPage < 1) {
            $firstPage = 1;
        }
        
        return $firstPage;
    }
    
    public function getLastPageInRange()
    {
        $totalPages = $this->getTotalPages();
        $pagesRange = $this->getPagesRange();
        $firstPage = $this->getFirstPageInRange();
        
        $lastPage = ($firstPage - 1) + $pagesRange;
        
        if ($lastPage > $totalPages) {
            $lastPage = $totalPages;
        }
        
        return $lastPage;
    }
    
    public function getTotalResults()
    {
        if (!$this->getIsInitialized()) {
            throw new \Exception('Paginator has to be initialized first. Call init() method.');
        }
        
        $model = $this->getModel();
        $select = clone $this->getSelect();
        $select->reset(\Laminas\Db\Sql\Select::ORDER);

        $select->columns(['count' => new \Laminas\Db\Sql\Expression("COUNT(1)")]);

        $row = $model->fetchRow($select);

        return $row->count;
    }
        
    /**
     * Pobierz listę wyników
     * @return \Laminas\Db\ResultSet\ResultSet
     */
    public function getData()
    {
        if (!$this->getIsInitialized()) {
            throw new \Exception('Paginator has to be initialized first. Call init() method.');
        }
        
        $itemsPerPage = $this->getItemsPerPage();
        $currentPage = $this->getCurrentPage();
        $sortingData = $this->getSortingData();
        $defaultSorting = $this->getDefaultSorting();
        
        $model = $this->getModel();
        $select = clone $this->getSelect();
        
        $select->limit($itemsPerPage)
                ->offset(($currentPage - 1) * $itemsPerPage);
        
        if (!empty($sortingData)) {
            $select->order($sortingData);
        } else if (!empty($defaultSorting)) {
            $select->order($defaultSorting);
        }
        
        $microtime = microtime(true);
        
        $data = $model->fetchAll($select);
        
        $this->setPageLoadTimeMs(microtime(true) - $microtime);
        
        return $data;
    }
    
    /**
     * Pobierz formularz filtra
     * @return \Base\Form\AbstractForm
     * @throws \Exception
     */
    public function getFilterForm()
    {
        $form = null;
        $filterFormName = $this->getFilterFormName();
        
        if (!empty($filterFormName)) {
            $form = $this->getServiceManager()->get($filterFormName);
            
            if (!$form instanceof Form\AbstractForm) {
                throw new \Exception(sprintf("Filter form class has to extends %s", Form\AbstractForm::class));
            }
        }
        
        return $form;
    }
    
    public function setFilterData($data)
    {
        $container = $this->getStorageContainer();
        
        foreach ($data as $key => $value) {
            $container->{$key} = $value;
        }
    }
    
    public function getFilterData()
    {
        $return = [];
        
        $container = $this->getStorageContainer();
        
        foreach ($container as $key => $value) {
            $return[$key] = $value;
        }
        
        return $return;
    }
    
    /**
     * Pobierz dane sortowania w postaci tablicy, gdzie kluczem jest nazwa kolumny, a wartością kolejność sortowania ASC lub DESC
     * @return array
     */
    public function getSortingData()
    {
        $return = [];
        
        $filterData = $this->getFilterData();
        
        if (isset($filterData['sort'])) {
            $return = $filterData['sort'];
        }
        
        return $return;
    }
    
    public function clearFilterData()
    {
        $container = $this->getStorageContainer();
        $name = $container->getName();
        
        $container->getManager()->getStorage()->clear($name);
    }
    
    /**
     * Pobierz nazwę kontenera przechowującego dane tego paginatora
     * @return string
     */
    public function getStorageContainerName()
    {
        return $this->getPaginatorId();
    }
    
    /**
     * Pobierz obiekt modelu
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    protected function getModel()
    {
        $modelName = $this->getModelName();
        
        if (empty($modelName)) {
            throw new \Exception('You have to provide model name');
        }
        
        $model = $this->getServiceManager()->get($modelName);
        /* @var $model \Base\Db\Table\AbstractModel */
        
        $prototype = $this->getArrayObjectPrototype();
        
        if (!empty($prototype)) {
            $prototypeClass = new $prototype();
            
            if (!$prototypeClass instanceof \Base\Db\Table\AbstractEntity) {
                throw new \Exception(sprintf("Klasa %s musi dziedziczyć po %s", $prototype, \Base\Db\Table\AbstractEntity::class));
            }
            
            $model->setArrayObjectPrototype($prototype);
        }
        
        if (!$model instanceof Db\Table\AbstractModel) {
            throw new \Exception(sprintf('Model have to extend %s class', Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
    
    protected function setIsInitialized($isInitialized)
    {
        $this->isInitialized = $isInitialized;
    }
    
    protected function initSelect()
    {
        $model = $this->getModel();
        $select = $model->select();
        
        $this->setSelect($select);
    }
    
    /**
     * Pobierz obiekt sesji przechowujący dane filtra
     * @return \Laminas\Session\Container
     */
    protected function getStorageContainer()
    {
        $containerName = $this->getStorageContainerName();

        $container = new \Laminas\Session\Container($containerName);
        
        return $container;
    }
}
