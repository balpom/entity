<?php

declare(strict_types=1);

namespace Balpom\Entity\Structures;

class Id extends AbstractStructure
{
    protected static function fields(): void
    {
        self::$field['id'] = ['string' => true];
    }

    public function isEqualTo(self $other): bool
    {
        return $this->getId() === $other->getId();
    }

    public function __construct(...$values)
    {
        if (1 <> count($values)) {
            throw new StructureCreationException('ID creation error.');
        }
        parent::__construct(...$values);
    }
}
