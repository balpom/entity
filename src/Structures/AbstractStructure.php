<?php

declare(strict_types=1);

namespace Balpom\Entity\Structures;

use Balpom\Entity\StructureInterface;

abstract class AbstractStructure implements StructureInterface
{
    protected array $enabledTypes = ['string', 'array', 'object', 'boolean', 'integer', 'double'];
    protected array $typesSynonyms = ['float' => 'double', 'bool' => 'boolean'];
    protected array $values = [];
    protected static $field = [];
    protected string $rootClass = '';
    protected array $definitionErrors = [];
    protected array $creationErrors = [];
    private array $hierarchy = [];
    private array $fields = [];
    protected static bool $createFromArray = false;

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
      self::$field['field4'] = ['array' => false];
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
        if (self::$createFromArray) {
            $key = array_key_first($values);
            if (0 !== $key) {
                $class = get_called_class();
                throw new StructureCreationException('Incorrect array for ' . $class . ' class creation.');
            }
            $values = $values[$key];
            self::$createFromArray = false;
        }

        $this->init();
        $this->fill($values);
    }

    /*
     * Additional third way to create Structure is createFromArray() method usage:
     *
     * $values = ['field1' => 'value1', 'field2' => null, 'field3' => 7777777];
     * $structure = Test::createFromArray($values);
     *
     */
    public static function createFromArray(array $values): StructureInterface
    {
        self::$createFromArray = true;
        $class = get_called_class();
        return new $class($values);
    }

    public function hasField(string|int|float $field): bool
    {
        $fields = $this->getFields();

        return isset($fields[$field]);
    }

    public function __call(string $method, array $params): string|array|object|bool|int|float|null
    {
        if ('get' !== substr($method, 0, 3) || 'get' === $method) {
            throw new StructureUsageException('Method ' . $method . ' not exists.');
        }

        //$first = substr($method, 3, 1);
        //$capital = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        /*
          if (!str_contains($capital, $first)) {
          throw new StructureUsageException('Incorrrect method name.');
          }
         */
        ////$field = lcfirst(substr($method, 3));
        $field = substr($method, 3);
        $sanitizedField = mb_strtolower($field);

        if (!array_key_exists($sanitizedField, $this->values)) {
            throw new StructureUsageException('Field ' . $field . ' not exists.');
        }

        return $this->values[$sanitizedField];
    }

    public function getFields(): array
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
                    //$field = mb_strtolower($field);
                    $type = key($properties);
                    if (isset($this->typesSynonyms[$type])) {
                        $notNull = $properties[$type];
                        $type = $this->typesSynonyms[$type];
                        $properties = [];
                        $properties[$type] = $notNull;
                    }
                    $fields[$field] = $properties;
                }
            }
        }
        /*
          foreach ($fields as $field => $properties) {
          $field = mb_strtolower($field);
          $this->fields[$field] = $properties;
          }
         */
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
        $values = $this->sanitizeValues($values, $fields);
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
        $fieldsCount = count($fields);
        $valuesCount = count($values);

        if ($fieldsCount < $valuesCount) {
            throw new StructureCreationException('Values number must be less or equal ' . $fieldsCount . ', but ' . $valuesCount . ' given.');
        }

        $hasErrors = false;

        if (0 === array_key_first($values)) {
            $num = 0;
            while ('integer' === gettype(key($values))) {
                $value = array_shift($values);
                $field = key($fields);
                $sanitizedField = mb_strtolower($field);
                $parameters = array_shift($fields);

                $checkResult = $this->checkValues($field, $value, $parameters);
                if (!$hasErrors && !$checkResult) {
                    $hasErrors = true;
                }

                if (!$hasErrors) {
                    $this->values[$sanitizedField] = $value;
                }

                //echo 'NNN ' . $field . ' => ' . $value . PHP_EOL;
            }
        }

        if ('string' === gettype(array_key_first($values))) {
            foreach ($fields as $field => $parameters) {
                $sanitizedField = mb_strtolower($field);
                if (array_key_exists($sanitizedField, $values)) {
                    $value = $values[$sanitizedField];
                    unset($values[$sanitizedField]);
                } else {
                    $value = null;
                }

                $checkResult = $this->checkValues($field, $value, $parameters);
                if (!$hasErrors && !$checkResult) {
                    $hasErrors = true;
                }

                if (!$hasErrors) {
                    $this->values[$sanitizedField] = $value;
                }

                //echo 'SSS ' . $field . ' => ' . $value . PHP_EOL;
            }
        }

        if (!empty($values)) {
            $excessiveFields = array_keys($values);
            $excessiveFields = implode(',', $excessiveFields);
            throw new StructureCreationException('Unknown error - excessive fields: ' . $excessiveFields);
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

    private function checkValues(int|string $field, mixed $value, array $parameters): bool
    {
        $noErrors = true;
        $type = key($parameters);
        $required = $parameters[$type]; // TRUE | FALSE (NOT NULL | may be NULL)
        $givenType = gettype($value);

        if (null === $value && !$required) {
            return $noErrors;
        }

        $definitionError = false;
        $creationError = false;

        if ('object' !== $givenType && !('string' === gettype($type) && class_exists($type))) {
            if (!in_array($type, $this->enabledTypes)) {
                $definitionError = 'Field ' . $field . ' has not enables type ' . $type . '.';
            }
            if ('NULL' === $givenType) {
                if ($required) {
                    if ('integer' === gettype($field)) {
                        $creationError = 'Value number ' . $field . ' must be NOT NULL.';
                    } else {
                        $creationError = 'Value with field ' . $field . ' must be NOT NULL.';
                    }
                }
            } elseif ($type !== $givenType) {
                if ('integer' === gettype($field)) {
                    $creationError = 'Value number ' . $field . ' must be ' . $type . ', ' . $givenType . ' given.';
                } else {
                    $creationError = 'Value with field ' . $field . ' must be ' . $type . ', ' . $givenType . ' given.';
                }
            }
        } else {
            $givenClass = get_class($value);
            if (!is_a($value, $this->rootClass)) {
                if ('integer' === gettype($field)) {
                    $definitionError = 'Value number ' . $field . ' must be ' . $this->rootClass . ' type, ' . $givenClass . ' given.';
                } else {
                    $definitionError = 'Value with field ' . $field . ' must be ' . $this->rootClass . ' type, ' . $givenClass . ' given.';
                }
            }
            if ($type !== $givenClass && !(class_exists($givenClass) && is_a($value, $type))) {
                if ('integer' === gettype($field)) {
                    $creationError = 'Value number ' . $field . ' must be ' . $type . ', ' . $givenClass . ' given.';
                } else {
                    $creationError = 'Value with field ' . $field . ' must be ' . $type . ', ' . $givenClass . ' given.';
                }
            }
        }

        if ($definitionError) {
            $noErrors = false;
            $this->definitionErrors[] = $definitionError;
        }
        if ($creationError) {
            $noErrors = false;
            $this->creationErrors[] = $creationError;
        }

        return $noErrors;
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

    final protected function sanitizeValues(array $values, array $fields): array
    {
        $sanitizedValues = [];
        foreach ($values as $field => $value) {
            $type = gettype($field);
            if ('integer' === $type) {
                $sanitizedValues[$field] = $value;
                continue;
            }
            if ('string' === $type) {
                if (!isset($fields[$field])) {
                    $this->creationErrors[] = 'Unknown field: ' . $field;
                }
                $field = mb_strtolower($field);
                $sanitizedValues[$field] = $value;
                continue;
            }
            $this->creationErrors[] = 'Illegal field type ' . $type . ' for field ' . (string) $field;
        }

        return $sanitizedValues;
    }

}
