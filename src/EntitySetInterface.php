<?php

declare(strict_types=1);

namespace Balpom\Entity;

use Balpom\Entity\StructureCollectionInterface;
use Balpom\Entity\Structures\Id;
use Balpom\Entity\Entities\AbstractEntity;

interface EntitySetInterface extends StructureCollectionInterface
{
    public function has(Id $id): bool;
    public function get(Id $id): AbstractEntity;
}
