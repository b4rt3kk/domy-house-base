<?php

namespace Base\Navigation;

class BrandIcon
{
    protected $src;
    
    protected $width = 32;
    
    protected $height = 32;
    
    protected $title;
    
    protected $routeName;
    
    protected $action = 'index';
    
    protected $params = [];
    
    public function getSrc()
    {
        return $this->src;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setSrc($src): void
    {
        $this->src = $src;
    }

    public function setWidth($width): void
    {
        $this->width = $width;
    }

    public function setHeight($height): void
    {
        $this->height = $height;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function setRouteName($routeName): void
    {
        $this->routeName = $routeName;
    }

    public function setAction($action): void
    {
        $this->action = $action;
    }
    
    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params): void
    {
        $this->params = $params;
    }
}
