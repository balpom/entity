<?php

declare(strict_types=1);

namespace Balpom\Entity;

use Balpom\Entity\StructureInterface;

interface StructureCollectionInterface extends \Iterator, \ArrayAccess, \Countable
{
    public function add(StructureInterface $structure): void;
    public function current(): StructureInterface;
    public function key(): mixed;
    public function next(): void;
    public function rewind(): void;
    public function valid(): bool;
    public function offsetExists(mixed $offset): bool;
    public function offsetGet(mixed $offset): StructureInterface|null;
    public function offsetSet(mixed $offset, mixed $value): void;
    public function offsetUnset(mixed $offset): void;
    public function count(): int;
}
