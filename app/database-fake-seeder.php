<?php


require 'vendor/autoload.php';

global $pdo;
global $faker;

$pdo = new PDO(
    sprintf("pgsql:host=%s;port=5432;dbname=%s;user=%s;password=%s",
        getenv('POSTGRES_HOST'),
        getenv('POSTGRES_DB'),
        getenv('POSTGRES_USER'),
        getenv('POSTGRES_PASSWORD'))
);

$faker = Faker\Factory::create();

for ($i = 1; $i <= 10; $i++) {
    createClient($i);
    createMerchandise($i);
}

function createClient(int $clientId): void
{
    global $faker;
    global $pdo;

    $sql = 'insert into clients(id, name) VALUES (:clientId, :clientName)';

    $statement = $pdo->prepare($sql);

    $statement->bindValue(':clientId', $clientId, PDO::PARAM_INT);
    $statement->bindValue(':clientName', $faker->unique()->name());

    $statement->execute();
}

function createMerchandise(int $merchandiseId): void
{
    global $faker;
    global $pdo;

    $sql = 'insert into merchandise(id, name) VALUES (:merchandiseId, :merchandiseName)';

    $statement = $pdo->prepare($sql);

    $statement->bindValue(':merchandiseId', $merchandiseId, PDO::PARAM_INT);
    $statement->bindValue(':merchandiseName', $faker->unique()->text(20));

    $statement->execute();
}