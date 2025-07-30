<?php

ini_set('max_execution_time', '0');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', 'off');
error_reporting(E_ALL);

include dirname(__DIR__) . '/vendor/autoload.php';

use Balpom\Entity\Structures\AbstractStructure;
use Balpom\Entity\Collections\StructureCollection;

class Person extends AbstractStructure
{
    protected static function fields(): void
    {
        self::$field['name'] = ['string' => true];
        self::$field['surname'] = ['string' => true];
    }

}

$collection = new StructureCollection();
$collection->add(new Person(name: "Vasya", surname: "Pupkin")); // 0
$collection->add(new Person(name: "Kolya", surname: "Morkovkin")); // 1
$collection->add(new Person(name: "Petya", surname: "Vasechkin")); // 2
$collection->add(new Person(name: "John", surname: "Doe")); // 3
$collection->add(new Person(name: "Donald", surname: "Smith")); //4

foreach ($collection as $key => $structure) {
    echo $key . ': ' . $structure->getName() . ' ' . $structure->getSurname() . PHP_EOL;
}

echo PHP_EOL;

echo 'WAS: ' . $collection[2]->getName() . ' ' . $collection[2]->getSurname() . PHP_EOL;
$collection[2] = new Person(name: "Evil", surname: "Dragon");
echo 'BECAME: ' . $collection[2]->getName() . ' ' . $collection[2]->getSurname() . PHP_EOL;

echo PHP_EOL;

try {
    $collection[2] = new stdClass();
} catch (Exception $e) {
    echo $e->getMessage();
}

echo PHP_EOL;
