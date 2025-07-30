<?php

declare(strict_types=1);

namespace Balpom\Entity;

interface EntityInterface extends StructureInterface
{
    public function getId(): string|int;
}
