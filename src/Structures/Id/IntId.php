<?php

declare(strict_types=1);

namespace Balpom\Entity\Structures\Id;

use Balpom\Entity\Structures\Id;

class IntId extends Id
{
    protected static function fields(): void
    {
        self::$field['id'] = ['integer' => true];
    }
}
