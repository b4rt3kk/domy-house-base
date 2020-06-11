<?php
namespace Base\Navigation\Page;

class Mvc extends \Laminas\Navigation\Page\Mvc
{
    protected $row;
    
    protected $serviceManager;
    
    public function __construct($options = null)
    {
        if (array_key_exists('row', $options)) {
            $this->setRow($options['row']);
        }
        
        if (array_key_exists('serviceManager', $options)) {
            $this->setServiceManager($options['serviceManager']);
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
        $rbacManager = $this->getServiceManager()->get(\Base\Services\Rbac\RbacManager::class);
        /* @var $rbacManager \Base\Services\Rbac\RbacManager */
        
        return $rbacManager->isGranted(null, $this->getPrivilege(), $this->getRow()->getData());
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
