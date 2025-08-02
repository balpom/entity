<?php

declare(strict_types=1);

namespace Balpom\Entity\Entities;

use Balpom\Entity\Structures\Id\IntId;

abstract class AbstractIntIdEntity extends AbstractEntity
{
    protected static function fields(): void
    {
        self::$field['id'] = [IntId::class => true];
    }

}
