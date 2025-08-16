<?php

declare(strict_types=1);

namespace Balpom\Entity;

interface StructureInterface
{
    /**
     *
      protected static function fields(): void
      {
      self::$field['title'] = ['string' => true];
      self::$field['description'] = ['string' => false];
      self::$field['content'] = ['string' => false];
      }
     *
     * getFields() returns:
      array [
      'title' => ['string' => true],
      'description' => ['string' => false],
      'content' => ['string' => false]
      ]
     */
    public function getFields(): array;

    /*
     * Returns TRUE if $field exists, returns FALSE if $field not exist.
     */
    public function hasField(string|int|float $field): bool;
}
