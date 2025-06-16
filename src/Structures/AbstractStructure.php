<?php

declare(strict_types=1);

namespace Balpom\Entity\Structures;

use Balpom\Entity\StructureInterface;

abstract class AbstractStructure implements StructureInterface
{
    protected array $enabledTypes = ['string', 'array', 'object', 'bool', 'integer', 'float'];
    protected array $values = [];
    protected static $field = [];
    protected string $rootClass = '';
    protected array $definitionErrors = [];
    protected array $creationErrors = [];
    private array $hierarchy = [];
    private array $fields = [];

    /*
     * Simple sample, what showing as Structure fields is defined.
     *
      protected static function fields(): void
      {
      self::$field['title'] = ['string' => true];        // TRUE - field must be NOT NULL.
      self::$field['description'] = ['string' => false]; // FALSE - field may be NULL.
      self::$field['content'] = ['string' => false];
      self::$field['dummy'] = [Dummy::class => false];
      }
     */
    protected static function fields(): void
    {

    }

    /*
     * There are two different ways to create Structure.
     * As sample, take this simple classes named Test and Parent.
     *

      class Parent extends AbstractStructure
      {
      protected static function fields(): void
      {
      self::$field['field1'] = ['string' => true];
      self::$field['field2'] = ['integer' => false];
      }
      }

      class Test extends Parent
      {
      protected static function fields(): void
      {
      self::$field['field3'] = ['integer' => true];
      self::$field['field4'] = ['array' => true];
      self::$field['field5'] = ['bool' => false];
      }
      }

     *
     * First way - pass field values in constructor throw names parameters:
     *
      $test3 = new Test(
      field1: '111',
      field2: 777,
      field3: 000,
      field4: ['abc' => 'xyz', 0 => 1],
      field5: null,
      );

     *
     * Second way - pass field values in constructor directly:
     *
      $test4 = new Test('111', 777, 000, ['abc' => 'xyz', 0 => 1], null);
     *
     * or
     *
      $test4 = new Test('111', 777, 000, ['abc' => 'xyz', 0 => 1]);
     *
     */
    public function __construct(...$values)
    {
        $this->init();
        $this->fill($values);
    }

    final public function hasKey(string|int|float $key): bool
    {
        $fields = $this->getFields();

        return isset($fields[$key]);
    }

    public function __call(string $method, array $params): string|array|object|bool|int|float|null
    {
        if ('get' !== substr($method, 0, 3) || 'get' === $method) {
            throw new StructureUsageException('Method ' . $method . ' not exists.');
        }

        $first = substr($method, 3, 1);
        $capital = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (!str_contains($capital, $first)) {
            throw new StructureUsageException('Incorrrect method name.');
        }

        $field = lcfirst(substr($method, 3));
        if (!str_contains($capital, $first) || !array_key_exists($field, $this->values)) {
            throw new StructureUsageException('Field ' . $field . ' not exists.');
        }

        return $this->values[$field];
    }

    final public function getFields(): array
    {
        if (!empty($this->fields)) {
            return $this->fields;
        }

        $lastIndex = count($this->hierarchy) - 1;
        $fields = [];
        for ($i = $lastIndex; 0 <= $i; $i--) {
            $class = $this->hierarchy[$i];
            $classFields = $class::getStructureFields($class);
            if (!empty($classFields)) {
                foreach ($classFields as $field => $properties) {
                    $fields[$field] = $properties;
                }
            }
        }
        $this->fields = $fields;

        return $this->fields;
    }

    protected function init(): void
    {
        $class = $this;
        $this->hierarchy[] = get_class($this);
        while (!empty($parent = get_parent_class($class))) {
            $this->hierarchy[] = $parent;
            $class = $parent;
        }

        $this->rootClass = $class;
    }

    final protected function fill(array $values): void
    {
        $fields = $this->getFields();
        $this->checkValues($values, $fields);
        $fieldsCount = count($fields); // May be empty non abstract structure. :-)

        /*
         * For theoretically possibility, as sample:
         * class Test extends AbstractStructure {
          protected static function fields(): void { self::$field['xyz'] = ['string' => false]; }
          }
         * and it's creation (without optional argument) as new Test()
         */
        if (empty($values) && 0 < $fieldsCount) {
            for ($i = 0; $i < $fieldsCount; $i++) {
                $values[] = null;
            }
        }

        $this->fillValues($fields, $values);
    }

    final protected function fillValues(array $fields, array $values): void
    {
        if (0 === array_key_first($values)) {
            $num = 0;
        } else {
            $num = false;
        }
        $hasErrors = false;
        foreach ($fields as $field => $properties) {
            $definitionError = '';
            $creationError = '';

            if (false === $num) {
                $num = $field;
            }
            if (array_key_exists($field, $values)) {
                $value = $values[$field];
                $index = $field;
            } else {
                $index = $num;
            }

            $type = key($properties);
            $required = $properties[$type]; // TRUE | FALSE (NOT NULL | may be NULL)
            $value = $values[$index] ?? null;
            $givenType = gettype($value);

            if (!(null === $value && !$required)) {
                if ('object' !== $givenType && !('string' === gettype($type) && class_exists($type))) {
                    if (!in_array($type, $this->enabledTypes)) {
                        $definitionError = 'Field ' . $field . ' has not enables type ' . $type . '.';
                    }
                    if ('NULL' === $givenType) {
                        if ($required) {
                            if ('integer' === gettype($index)) {
                                $creationError = 'Value number ' . $index . ' must be NOT NULL.';
                            } else {
                                $creationError = 'Value with field ' . $index . ' must be NOT NULL.';
                            }
                        }
                    } elseif ($type !== $givenType) {
                        if ('integer' === gettype($index)) {
                            $creationError = 'Value number ' . $index . ' must be ' . $type . ', ' . $givenType . ' given.';
                        } else {
                            $creationError = 'Value with field ' . $index . ' must be ' . $type . ', ' . $givenType . ' given.';
                        }
                    }
                } else {
                    $givenClass = get_class($value);
                    if (!is_a($value, $this->rootClass)) {
                        if ('integer' === gettype($index)) {
                            $definitionError = 'Value number ' . $index . ' must be ' . $this->rootClass . ' type, ' . $givenClass . ' given.';
                        } else {
                            $definitionError = 'Value with field ' . $index . ' must be ' . $this->rootClass . ' type, ' . $givenClass . ' given.';
                        }
                    }
                    if ($type !== $givenClass && !(class_exists($givenClass) && is_a($value, $type))) {
                        if ('integer' === gettype($index)) {
                            $creationError = 'Value number ' . $index . ' must be ' . $type . ', ' . $givenClass . ' given.';
                        } else {
                            $creationError = 'Value with field ' . $index . ' must be ' . $type . ', ' . $givenClass . ' given.';
                        }
                    }
                }
            }
            $num++;

            if (!$hasErrors && ($definitionError || $creationError)) {
                $hasErrors = true;
            }
            if (!$hasErrors) {
                $this->values[$field] = $value;
            }

            if ($definitionError) {
                $this->definitionErrors[] = $definitionError;
            }
            if ($creationError) {
                $this->creationErrors[] = $creationError;
            }
        }

        if (!empty($this->definitionErrors)) {
            $definitionError = implode(PHP_EOL, $this->definitionErrors);
            throw new StructureDefinitionException($definitionError);
        }
        if (!empty($this->creationErrors)) {
            $creationError = implode(PHP_EOL, $this->creationErrors);
            throw new StructureCreationException($creationError);
        }
    }

    protected static function getStructureFields(string $class): array
    {
        $class::fields();
        $result = $class::$field;
        foreach ($result as $key => $value) {
            unset($class::$field[$key]);
        }

        return $result;
    }

    final protected function checkValues(array $values, array $fields): void
    {
        foreach ($values as $field => $value) {
            $type = gettype($field);
            if ('integer' === $type) {
                continue;
            }
            if ('string' === $type) {
                if (!isset($fields[$field])) {
                    $this->creationErrors[] = 'Unknown field: ' . $field;
                }
                continue;
            }
            $this->creationErrors[] = 'Illegal field type ' . $type . ' for field ' . (string) $field;
        }
    }
}
