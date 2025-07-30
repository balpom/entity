<?php

declare(strict_types=1);

namespace Balpom\Entity\Collections;

use Balpom\Entity\StructureInterface;
use Balpom\Entity\StructureCollectionInterface;

class StructureCollection implements StructureCollectionInterface
{
    const TYPE = StructureInterface::class; // May be changed in children classes.

    protected array $collection = [];
    protected array $index = [];
    private int $position = 0;

    public function add(StructureInterface $structure): void
    {
        $this->collection[] = $structure;
    }

    public function current(): StructureInterface
    {
        return $this->collection[$this->position];
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->collection[$this->position]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->collection[$offset]);
    }

    public function offsetGet(mixed $offset): StructureInterface|null
    {
        return isset($this->collection[$offset]) ? $this->collection[$offset] : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ('integer' !== gettype($offset)) {
            throw new StructureCollectionException('Given offset must be integer');
        }

        $class = StructureCollection::TYPE;
        if (!is_object($value) || !($value instanceof $class)) {
            throw new StructureCollectionException('Given value must be instance of ' . $class);
        }

        if (is_null($offset)) {
            $this->collection[] = $value;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->collection[$offset])) {
            unset($this->collection[$offset]);
        }
    }

}
