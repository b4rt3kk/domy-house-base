<?php
namespace Base;

abstract class Paginator extends Logic\AbstractLogic
{
    protected $modelName;
    
    protected $headers = [];
    
    protected $currentPage = 1;
    
    protected $itemsPerPage = 10;
    
    protected $pagesRange = 5;
    
    protected $select;
    
    protected $isInitialized = false;
    
    public function init()
    {
        $this->initSelect();
        
        $this->setIsInitialized(true);
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
        return $this->itemsPerPage;
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
        
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
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
    
    public function getIsInitialized()
    {
        return $this->isInitialized;
    }
    
    /**
     * Pobierz liczbę stron paginatora
     * @return integer
     * @throws \Exception
     */
    public function getTotalPages()
    {
        if (!$this->getIsInitialized()) {
            throw new \Exception('Paginator has to be initialized first. Call init() method.');
        }
        
        $itemsPerPage = $this->getItemsPerPage();
        
        $model = $this->getModel();
        $select = clone $this->getSelect();
        $select->reset(\Laminas\Db\Sql\Select::ORDER);
        
        $select->columns(['count' => new \Laminas\Db\Sql\Expression("COUNT(1)")]);
        
        $row = $model->fetchRow($select);
        
        return ceil($row->count / $itemsPerPage);
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
        
        $model = $this->getModel();
        $select = clone $this->getSelect();
        
        $select->limit($itemsPerPage)
                ->offset(($currentPage - 1) * $itemsPerPage);
        
        $data = $model->fetchAll($select);
        
        return $data;
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
}
