<?php

declare(strict_types=1);

namespace Balpom\Entity\Structures\Id;

use Balpom\Entity\Structures\Id;
use Ramsey\Uuid\Uuid as UuidGenerator;

class Uuid extends Id
{
    public static function next(): self
    {
        return new self(UuidGenerator::uuid4()->toString());
    }
}
