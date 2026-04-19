<?php
namespace Base\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractHelperFactory implements FactoryInterface
{
    /**
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return \Base\View\Helper\AbstractHelper
     * @throws Exception
     * @throws \Exception
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (!class_exists($requestedName)) {
            throw new \Exception(sprintf("Klasa %s nie istnieje", $requestedName));
        }

        $helper = new $requestedName();
        /* @var $helper \Base\View\Helper\AbstractHelper */

        if (!$helper instanceof \Base\View\Helper\AbstractHelper) {
            throw new \Exception(sprintf("Helper musi dziedziczyć po %s", \Base\View\Helper\AbstractHelper::class));
        }

        $helper->setServiceManager($container);

        return $helper;
    }
}
