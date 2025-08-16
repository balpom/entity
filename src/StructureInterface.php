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

    /*
     * getField('title') returns:
      array ['string' => true]
     */
    public function getField(string|int|float $field): array;

    /*
     * getFieldType('title') returns: 'string'
     * Returning value may be only 'string', 'array', 'object', 'bool', 'int', 'float'
     */
    public function getFieldType(string|int|float $field): string;

    /*
     * isFieldMandatory('title') returns: true
     * isFieldMandatory('description') returns: false
     */
    public function isFieldMandatory(string|int|float $field): bool;
}
