<?php

namespace Base\Services\Image;

class Converter
{
    /**
     * @var Image
     */
    protected Image $sourceImage;
    
    public function getSourceImage(): Image
    {
        return $this->sourceImage;
    }

    public function setSourceImage(Image $sourceImage): void
    {
        $this->sourceImage = $sourceImage;
    }
}
