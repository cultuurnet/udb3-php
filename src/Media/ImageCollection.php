<?php

namespace CultuurNet\UDB3\Media;

use TwoDotsTwice\Collection\AbstractCollection;
use TwoDotsTwice\Collection\CollectionInterface;

class ImageCollection extends AbstractCollection implements CollectionInterface
{
    /**
     * @var Image|null
     */
    protected $mainImage;

    protected function getValidObjectType()
    {
        return Image::class;
    }

    /**
     * @param Image $image
     * @return ImageCollection
     */
    public function withMain(Image $image)
    {
        $collection = $this->contains($image) ? $this : $this->with($image);

        $copy = clone $collection;
        $copy->mainImage = $image;
        return $copy;
    }

    /**
     * @return Image|null
     */
    public function getMain()
    {
        if (0 === $this->length()) {
            return null;
        }

        return $this->mainImage ? $this->mainImage : $this->getIterator()->offsetGet(0);
    }
}
