<?php

ini_set('max_execution_time', '0');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', 'off');
error_reporting(E_ALL);

include dirname(__DIR__) . '/vendor/autoload.php';

use Balpom\Entity\Structures\AbstractStructure;

class Currency extends AbstractStructure
{
    protected static function fields(): void
    {
        self::$field['name'] = ['string' => true];
        self::$field['rate'] = ['integer|double' => true];
    }

}

$rouble = new Currency('RUR', 1);
$euro = new Currency('EUR', 99.77);

echo $rouble->getName() . ' rate ' . $rouble->getRate() . PHP_EOL;
echo gettype($rouble->getRate()) . PHP_EOL;
echo PHP_EOL;
echo $euro->getName() . ' rate ' . $euro->getRate() . PHP_EOL;
echo gettype($euro->getRate()) . PHP_EOL;
