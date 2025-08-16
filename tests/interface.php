<?php

ini_set('max_execution_time', '0');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', 'off');
error_reporting(E_ALL);

include dirname(__DIR__) . '/vendor/autoload.php';

use Balpom\Entity\Structures\AbstractStructure;

class Person extends AbstractStructure
{
    protected static function fields(): void
    {
        self::$field['name'] = ['string' => true];
        self::$field['surname'] = ['string' => false];
    }

}

function booler(bool $value): string
{
    if (true === $value) {
        return 'TRUE';
    }

    return 'FALSE';
}

$person = new Person(name: "Vasya", surname: "Pupkin");

print_r($person->getFields());
echo PHP_EOL;

echo booler($person->hasField('name')); // TRUE
echo PHP_EOL;
echo booler($person->hasField('xyz')); // FALSE
echo PHP_EOL;

print_r($person->getField('name')); // Array ( [string] => 1 )
echo PHP_EOL;
print_r($person->getField('surname')); // Array ( [string] => )
echo PHP_EOL;

echo $person->getFieldType('name'); // string
echo PHP_EOL;
try {
    echo $person->getFieldType('XYZ');
} catch (\Exception $e) {
    echo $e->getMessage(); // Field XYZ not exists.
}
echo PHP_EOL;

echo booler($person->isFieldMandatory('name')); // TRUE
echo PHP_EOL;
echo booler($person->isFieldMandatory('surname')); // FALSE
echo PHP_EOL;
