<?php

namespace Base\Navigation;

class Breadcrumbs extends \Base\Logic\AbstractLogic
{
    protected $navigation;
    
    protected $currentRoute;
    
    protected $currentAction;
    
    protected $minDepth = 0;
    
    /**
     * 
     * @return \Base\Navigation\Navigation
     */
    public function getNavigation()
    {
        return $this->navigation;
    }

    public function setNavigation(\Base\Navigation\Navigation $navigation)
    {
        $this->navigation = $navigation;
    }

    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function getCurrentAction()
    {
        return $this->currentAction;
    }

    public function setCurrentRoute($currentRoute)
    {
        $this->currentRoute = $currentRoute;
    }

    public function setCurrentAction($currentAction)
    {
        $this->currentAction = $currentAction;
    }
    
    public function getMinDepth()
    {
        return $this->minDepth;
    }

    public function setMinDepth($minDepth)
    {
        $this->minDepth = $minDepth;
    }
    
    /**
     * Pobierz listę stron
     * @return \Base\Navigation\Page\Mvc[]
     */
    public function getPages()
    {
        $pages = [];
        $parentPage = $this->getCurrentPage();
        
        if (!empty($parentPage)) {
            $parentPage->setActive(true);
            
            $pages = [
                $parentPage,
            ];

            while (($parentPage = $this->findParentPage($parentPage, $this->getNavigation()->getPages())) !== null) {
                $pages[] = $parentPage;
            }
        }
        
        return array_reverse($pages);
    }
    
    /**
     * Pobierz aktualnie otwartą stronę
     * @return \Base\Navigation\Page\Mvc
     */
    public function getCurrentPage()
    {
        $navigation = $this->getNavigation();
        $currentRoute = $this->getCurrentRoute();
        $currentAction = $this->getCurrentAction();
        
        $currentPage = $this->findPage($navigation->getPages(), $currentRoute, $currentAction);
        
        return $currentPage;
    }
    
    /**
     * Znajdź stronę z podanej listy stron o podanym route i action
     * @param \Base\Navigation\Page\Mvc[] $pages
     * @param string $routeName
     * @param string $actionName
     * @return \Base\Navigation\Page\Mvc|null
     */
    protected function findPage($pages, $routeName, $actionName, $recursive = true)
    {
        $foundPage = null;
        
        foreach ($pages as $page) {
            /* @var $page \Base\Navigation\Page\Mvc */
            $pageRouteName = $page->getRoute();
            $pageActionName = $page->getAction();
            
            if ($pageRouteName === $routeName && $pageActionName === $actionName) {
                $foundPage = $page;
                break;
            }
            
            if (empty($foundPage) && $page->hasPages() && $recursive) {
                // ta strona posiada podstrony
                $foundPage = $this->findPage($page->getPages(), $routeName, $actionName);
                
            }
        }
        
        return $foundPage;
    }
    
    /**
     * Znajdź rodzica dla podanej strony
     * @param \Base\Navigation\Page\Mvc $page
     * @param \Base\Navigation\Page\Mvc[] $pages
     * @return \Base\Navigation\Page\Mvc
     */
    protected function findParentPage(\Base\Navigation\Page\Mvc $page, $pages)
    {
        $foundPage = null;
        
        foreach ($pages as $rowPage) {
            if ($rowPage->hasPages()) {
                foreach ($rowPage->getPages() as $childrenPage) {
                    if ($childrenPage->getRoute() === $page->getRoute() && $childrenPage->getAction() === $page->getAction()) {
                        $foundPage = $rowPage;
                    }
                }
                
                if (empty($foundPage)) {
                    $foundPage = $this->findParentPage($page, $rowPage->getPages());
                }
            }
        }
        
        return $foundPage;
    }
}

