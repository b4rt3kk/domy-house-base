<?php
namespace Base\Navigation\Page;

class Mvc extends \Laminas\Navigation\Page\Mvc
{
    protected $row;
    
    protected $serviceManager;
    
    protected $where = [];
    
    protected $class;
    
    protected $badgeObjectClass;
    
    protected $icon;
    
    protected $id;
    
    protected $url;
    
    protected $attributesString;
    
    protected $attributes = [];
    
    protected $htmlTitle;
    
    public function __construct($options = null)
    {
        if (array_key_exists('row', $options)) {
            $this->setRow($options['row']);
        }
        
        if (array_key_exists('serviceManager', $options)) {
            $this->setServiceManager($options['serviceManager']);
        }
        
        if (array_key_exists('where', $options)) {
            $this->setWhere($options['where']);
        }
        
        if (array_key_exists('attributes', $options)) {
            $this->setAttributes($options['attributes']);
        }
        
        $options['params'] = $this->prepareParams($options);
        
        $application = $this->getServiceManager()->get('Application');
        /* @var $event \Laminas\Mvc\Application */
        
        $router = $application->getMvcEvent()->getRouter();
        
        $this->setRouter($router);
        
        parent::__construct($options);
    }
    
    public function getHtmlTitle()
    {
        return $this->htmlTitle;
    }

    public function setHtmlTitle($htmlTitle)
    {
        $this->htmlTitle = $htmlTitle;
    }

    public function getAttributesString()
    {
        return $this->attributesString;
    }

    public function setAttributesString($attributesString)
    {
        $this->attributesString = $attributesString;
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    /**
     * Pobierz listę atrybutów w postaci stringa do podstawienia w elemencie HTML
     * @return string
     */
    public function getAttributesAsString()
    {
        $return = null;
        $attributes = $this->getAttributes();
        
        foreach ($attributes as $name => $value) {
            $return .= $name . '="' . $value . '" ';
        }
        
        return $return;
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id = null)
    {
        $this->id = $id;
    }
        
    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }
    
    public function getBadgeObjectClass()
    {
        return $this->badgeObjectClass;
    }

    public function setBadgeObjectClass($badgeObjectClass)
    {
        $this->badgeObjectClass = $badgeObjectClass;
    }
    
    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    /**
     * @return \Base\Navigation\Page\AbstractBadge|null
     */
    public function getBadge()
    {
        $badge = null;
        $serviceManager = $this->getServiceManager();
        $className = $this->getBadgeObjectClass();
        
        if (!empty($className)) {
            $badge = $serviceManager->get($className);
            
            if (!$badge instanceof AbstractBadge) {
                throw new \Exception(sprintf("Obiekt do obsługi badges musi dziedziczyć po %s", AbstractBadge::class));
            }
        }
        
        return $badge;
    }
    
    /**
     * Wyrenderuj treść badge o ile obiekt został przekazany
     * @return string
     */
    public function renderBadge()
    {
        $return = null;
        // pobierz obiekt odpowiedzialny za renderowanie badge
        $badge = $this->getBadge();
        
        if (!empty($badge)) {
            $return = $badge->render();
        }
        
        return $return;
    }

    /**
     * @return \Base\Db\Table\AbstractEntity
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * @param \Base\Db\Table\AbstractEntity $row
     */
    public function setRow($row)
    {
        $this->row = $row;
    }
    
    public function getWhere()
    {
        return $this->where;
    }

    public function setWhere($where)
    {
        $this->where = $where;
    }
    
    /**
     * @return \Base\View\Helper\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class = null)
    {
        $this->class = $class;
    }
    
    public function isAllowed()
    {
        $isAllowed = false;
        $rbacManager = $this->getServiceManager()->get(\Base\Services\Rbac\RbacManager::class);
        /* @var $rbacManager \Base\Services\Rbac\RbacManager */
        
        $row = $this->getRow();
        $data = [];
        $privilege = $this->getPrivilege();
        
        if ($row instanceof \Base\Db\Table\AbstractEntity) {
            $data = $row->getData();
        }
        
        if (!empty($privilege)) {
            $isAllowed = $rbacManager->isGranted(null, $this->getPrivilege(), $data);
        }
        
        return $isAllowed;
    }
    
    /**
     * Sprawdź czy przycisk powinien zostać pokazany, na podstawie warunków wiersza. Wszystkie muszą zostać spełnione.
     * @return boolean
     */
    public function isShowable()
    {
        $showable = true;
        $whereConditions = $this->getWhere();
        $row = $this->getRow();
        
        if (!empty($whereConditions) && !empty($row)) {
            $conditionsMet = 0;
            
            foreach ($whereConditions as $columnName => $condition) {
                if ($row->{$columnName} == $condition) {
                    $conditionsMet++;
                }
            }
            
            $showable = sizeof($whereConditions) === $conditionsMet;
        }
        
        return $showable;
    }
    
    protected function prepareParams($options = [])
    {
        $return = [];
        $params = isset($options['params']) ? $options['params'] : [];
        $aliases = isset($options['aliases']) ? $options['aliases'] : [];
        $row = $this->getRow();
        
        foreach ($params as $name => $value) {
            $paramValue = $value;
            
            if (null === $value) {
                $paramValue = $row->{$name};
            }
            
            if (isset($aliases[$name])) {
                // istnieje alias dla tego parametru
                $return[$aliases[$name]] = $paramValue;
            } else {
                $return[$name] = $paramValue;
            }
        }
        
        return $return;
    }
}
