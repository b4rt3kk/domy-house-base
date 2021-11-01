<?php
namespace Base\Navigation;

class Navigation extends \Laminas\Navigation\Navigation
{
    protected $brandIcon;
    
    /**
     * @return \Base\Navigation\BrandIcon
     */
    public function getBrandIcon()
    {
        return $this->brandIcon;
    }

    public function setBrandIcon(BrandIcon $brandIcon)
    {
        $this->brandIcon = $brandIcon;
    }
}
