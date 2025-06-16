<?php

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
     */
    public function getFields(): array;
}
