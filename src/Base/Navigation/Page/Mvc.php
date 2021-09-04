<?php
namespace Base\Navigation\Page;

class Mvc extends \Laminas\Navigation\Page\Mvc
{
    protected $row;
    
    protected $serviceManager;
    
    protected $where = [];
    
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
        
        $options['params'] = $this->prepareParams($options);
        
        $application = $this->getServiceManager()->get('Application');
        /* @var $event \Laminas\Mvc\Application */
        
        $router = $application->getMvcEvent()->getRouter();
        
        $this->setRouter($router);
        
        parent::__construct($options);
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
        $params = $options['params'];
        $row = $this->getRow();
        
        foreach ($params as $name => $value) {
            $return[$name] = $value;
            
            if (null === $value) {
                $return[$name] = $row->{$name};
            }
        }
        
        return $return;
    }
}
