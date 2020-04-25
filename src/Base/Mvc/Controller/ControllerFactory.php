<?php
namespace Base\Mvc\Controller;

class ControllerFactory implements \Laminas\ServiceManager\Factory\FactoryInterface
{
    public function __invoke(\Interop\Container\ContainerInterface $container, $requestedName, array $options = null)
    {
        $class = new $requestedName($container);
        
        if (!$class instanceof AbstractActionController) {
            throw new \Exception(sprintf('Controller has to extend %s class', AbstractActionController::class));
        }
        
        return $class;
    }
}
