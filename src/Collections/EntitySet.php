<?php

declare(strict_types=1);

namespace Balpom\Entity\Collections;

use Balpom\Entity\EntitySetInterface;
use Balpom\Entity\EntityInterface;
use Balpom\Entity\Structures\Id;
use Balpom\Entity\Entities\AbstractEntity;
use Balpom\Entity\StructureInterface;

class EntitySet extends StructureCollection implements EntitySetInterface
{
    const TYPE = EntityInterface::class;

    protected array $index = [];

    public function has(Id $id): bool
    {
        $searchId = $id->getId();
        if (isset($this->index[$searchId])) {
            return true;
        }

        return false;
    }

    public function get(Id $id): AbstractEntity
    {
        $searchId = $id->getId();
        if (!isset($this->index[$searchId])) {
            throw new EntitySetException('Entity not found');
        }
        if (!isset($this->collection[$this->index[$searchId]])) {
            throw new EntitySetException('Unknown error: index exists, but entity not exist');
        }

        return $this->collection[$this->index[$searchId]];
    }

    public function add(StructureInterface $structure): void
    {
        $this->collection[] = $structure;
        $id = $structure->getId();
        $this->index[$id] = count($this->collection) - 1;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->checkOffsetSet($offset, $value);

        $id = $value->getId();

        if (is_null($offset)) {
            $this->collection[] = $value;
            $this->index[$id] = count($this->collection) - 1;
        } else {
            $this->collection[$offset] = $value;
            $this->index[$id] = $offset;
        }
    }

}
