<?php

declare(strict_types=1);

namespace Balpom\Entity\Entities;

use Balpom\Entity\EntityInterface;
use Balpom\Entity\Structures\AbstractStructure;
use Balpom\Entity\Structures\Id;

abstract class AbstractEntity extends AbstractStructure implements EntityInterface
{
    protected static function fields(): void
    {
        self::$field['id'] = [Id::class => true];
    }

    public function getId(): string|int
    {
        return $this->values['id']->getId();
    }

}
