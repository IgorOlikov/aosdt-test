<?php

require 'vendor/autoload.php';


$faker = Faker\Factory::create();

$stream = fopen('orders.txt', 'w+');

$arr = [
    [1,1, $faker->text(10)],
    [2,2, $faker->text(10)],
    [3,3, $faker->text(10)],
    [4,4, $faker->text(10)],
    [5,5, $faker->text(10)],
    [6,6, $faker->text(10)],
    [7,7, $faker->text(10)],
    [8,8, $faker->text(10)],
    [9,9, $faker->text(10)],
    [10,10, $faker->text(10)],
];

foreach ($arr as $order) {
    $str = implode(';', $order);
    $str .= "\n";
    fwrite($stream, $str);
}

for ($i = 0; $i < 1000; $i++) {
    $str = sprintf(
        '%d;%d;%s',
        $faker->randomDigitNotNull(),
        $faker->randomDigitNotNull(),
        $faker->text(10));

    $str .= "\n";

    fwrite($stream, $str);
}

for ($i = 0; $i < 100; $i++) {

    $str = $faker->text(30);
    $str .= "\n";

    fwrite($stream, $str);
}

for ($i = 0; $i < 100; $i++) {
    $str = sprintf(
        '%s;%d;%s',
        $faker->text(20),
        $faker->numberBetween(1, 200),
        $faker->text(10));

    $str .= "\n";

    fwrite($stream, $str);
}


fclose($stream);