<?php
namespace Base\Services\Rbac\Assertion;

interface AssertionCallableInterface
{
    public function __invoke(
            \Base\Services\Rbac\RbacAssertionManager $rbacAssertionManager,
            \Laminas\Permissions\Rbac\Rbac $rbac,
            string $permission,
            array $params = []
    );
}
