<?php

namespace Base\Config\Paginator;

class ViewConfig extends \Base\Object\AbstractHydratableObject
{
    protected $tableActionsPartial;
    
    protected $listPartial = 'base/table';
    
    protected $listPartialItem = null;
    
    protected $filterPartial = 'base/paginator_filter';
    
    protected $topNavigationPartial = 'base/paginator_navigation';
    
    protected $bottomNavigationPartial = 'base/paginator_navigation_bottom';
    
    protected $noResultsText = '<div class="card"><div class="card-body">Brak wyników</div></div>';
    
    protected $isResponsive = true;
    
    protected $ajaxUrl;
    
    protected $showFilter = true;
    
    protected $showTopNavigation = true;
    
    protected $showBottomNavigation = true;
    
    protected $rewriteQueryParams = false;
    
    public function getTableActionsPartial()
    {
        return $this->tableActionsPartial;
    }

    public function getListPartial()
    {
        return $this->listPartial;
    }

    public function getListPartialItem()
    {
        return $this->listPartialItem;
    }

    public function getNoResultsText()
    {
        return $this->noResultsText;
    }

    public function getIsResponsive()
    {
        return $this->isResponsive;
    }

    public function getAjaxUrl()
    {
        return $this->ajaxUrl;
    }

    public function setTableActionsPartial($tableActionsPartial): void
    {
        $this->tableActionsPartial = $tableActionsPartial;
    }

    public function setListPartial($listPartial): void
    {
        $this->listPartial = $listPartial;
    }

    public function setListPartialItem($listPartialItem): void
    {
        $this->listPartialItem = $listPartialItem;
    }

    public function setNoResultsText($noResultsText): void
    {
        $this->noResultsText = $noResultsText;
    }

    public function setIsResponsive($isResponsive): void
    {
        $this->isResponsive = $isResponsive;
    }

    public function setAjaxUrl($ajaxUrl): void
    {
        $this->ajaxUrl = $ajaxUrl;
    }
    
    public function getFilterPartial()
    {
        return $this->filterPartial;
    }

    public function getTopNavigationPartial()
    {
        return $this->topNavigationPartial;
    }

    public function getBottomNavigationPartial()
    {
        return $this->bottomNavigationPartial;
    }

    public function setFilterPartial($filterPartial): void
    {
        $this->filterPartial = $filterPartial;
    }

    public function setTopNavigationPartial($topNavigationPartial): void
    {
        $this->topNavigationPartial = $topNavigationPartial;
    }

    public function setBottomNavigationPartial($bottomNavigationPartial): void
    {
        $this->bottomNavigationPartial = $bottomNavigationPartial;
    }
    
    public function getShowFilter()
    {
        return $this->showFilter;
    }

    public function getShowTopNavigation()
    {
        return $this->showTopNavigation;
    }

    public function getShowBottomNavigation()
    {
        return $this->showBottomNavigation;
    }

    public function setShowFilter($showFilter): void
    {
        $this->showFilter = $showFilter;
    }

    public function setShowTopNavigation($showTopNavigation): void
    {
        $this->showTopNavigation = $showTopNavigation;
    }

    public function setShowBottomNavigation($showBottomNavigation): void
    {
        $this->showBottomNavigation = $showBottomNavigation;
    }
    
    public function getRewriteQueryParams()
    {
        return $this->rewriteQueryParams;
    }

    public function setRewriteQueryParams($rewriteQueryParams): void
    {
        $this->rewriteQueryParams = $rewriteQueryParams;
    }
}
