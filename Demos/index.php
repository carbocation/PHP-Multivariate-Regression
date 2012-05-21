<?php

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use Regression\Matrix;
use Regression\Regression;

$predictors = array(
    array(0,3,4),
    array(1,2,30),
    array(2,2,17),
    array(3,2,48),
    array(4,3,23),
    array(5,2,12),
    array(6,3,6),
    array(7,3,11),
    array(8,3,4),
    array(9,2,4),
    array(10,3,1),
    array(11,2,14),
    array(12,3,4),
);

$predicted = array(
    array(123),
    array(109),
    array(99),
    array(80),
    array(85),
    array(70),
    array(60),
    array(50),
    array(40),
    array(30),
    array(29),
    array(90),
    array(10),
);

$regression = new Regression();
$regression->setX(new Matrix($predictors));
$regression->setY(new Matrix($predicted));
$regression->exec();

echo "Coefficients:" . PHP_EOL;
print_r($regression->getCoefficients());

echo "StdErr:" . PHP_EOL;
print_r($regression->getStandardError());

echo "Coef P values:" . PHP_EOL;
print_r($regression->getPValues());
