<?php

namespace OrderCoreBundle\Service;

use Doctrine\Common\Collections\Collection;

class CollectionService
{
    /**
     * @template T
     * @param Collection<int|string, T> $collection
     * @param T $item
     */
    public function addToCollection(Collection $collection, mixed $item, callable $setter): void
    {
        if (!$collection->contains($item)) {
            $collection->add($item);
            $setter($item);
        }
    }

    /**
     * @template T
     * @param Collection<int|string, T> $collection
     * @param T $item
     */
    public function removeFromCollection(Collection $collection, mixed $item, callable $remover): void
    {
        if ($collection->removeElement($item)) {
            $remover($item);
        }
    }
}
