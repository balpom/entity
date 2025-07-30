<?php

ini_set('max_execution_time', '0');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', 'off');
error_reporting(E_ALL);

include dirname(__DIR__) . '/vendor/autoload.php';

use Balpom\Entity\Entities\AbstractEntity;
use Balpom\Entity\Structures\Id\IntId;
use Balpom\Entity\Collections\EntitySet;

class Person extends AbstractEntity
{
    protected static function fields(): void
    {
        self::$field['name'] = ['string' => true];
        self::$field['surname'] = ['string' => true];
    }

}

$collection = new EntitySet();
$collection->add(new Person(id: new IntId(id: 111), name: "Vasya", surname: "Pupkin")); // 0
$collection->add(new Person(id: new IntId(id: 222), name: "Kolya", surname: "Morkovkin")); // 1
$collection->add(new Person(id: new IntId(id: 333), name: "Petya", surname: "Vasechkin")); // 2
$collection->add(new Person(id: new IntId(id: 444), name: "John", surname: "Doe")); // 3
$collection->add(new Person(id: new IntId(id: 555), name: "Donald", surname: "Smith")); //4

foreach ($collection as $key => $entity) {
    echo $key . ': ' . $entity->getId() . ' - ' . $entity->getName() . ' ' . $entity->getSurname() . PHP_EOL;
}

echo PHP_EOL;

echo 'WAS: ' . $collection[2]->getId() . ' - ' . $collection[2]->getName() . ' ' . $collection[2]->getSurname() . PHP_EOL;
$newId = new IntId(id: 666);
$collection[2] = new Person(id: $newId, name: "Evil", surname: "Dragon");
echo 'BECAME: ' . $collection[2]->getId() . ' - ' . $collection[2]->getName() . ' ' . $collection[2]->getSurname() . PHP_EOL;

if ($collection->has($newId)) {
    echo 'Has this ID (' . $newId->getId() . ').' . PHP_EOL;
} else {
    echo "Don't has this ID." . PHP_EOL;
}

echo PHP_EOL;

try {
    $collection[2] = new stdClass();
} catch (Exception $e) {
    echo $e->getMessage();
}

echo PHP_EOL;
