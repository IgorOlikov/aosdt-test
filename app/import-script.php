<?php

echo 'Script running...' . PHP_EOL;

global $pdo;

$pdo = new PDO(
        sprintf("pgsql:host=%s;port=5432;dbname=%s;user=%s;password=%s",
            getenv('POSTGRES_HOST'),
            getenv('POSTGRES_DB'),
            getenv('POSTGRES_USER'),
            getenv('POSTGRES_PASSWORD'))
        );

$ordersFilePath = __DIR__ . '/' . $argv[1];

$ordersResource = fopen($ordersFilePath, 'r');
$logResource = fopen('log', 'w+');

const MAX_CHUNK_SIZE = 500;

$chunkSize = 0;
$ordersChunk = [];


while (($orderLine = fgets($ordersResource)) !== false) {

    $orderLine = str_replace("\n",'', $orderLine);

    $validatedOrder = validateOrderLine($orderLine);

    if (!empty($validatedOrder['error'])) {
        logErrors($logResource, sprintf("%s error:%s", $orderLine, $validatedOrder['error']));
    } else {
        $ordersChunk[] = $validatedOrder['order'];
        $chunkSize++;
    }

    if ($chunkSize > MAX_CHUNK_SIZE) {

        saveOrdersChunk($ordersChunk);

        $chunkSize = 0;
        $ordersChunk = [];
    }
}

if (!empty($ordersChunk)) {
    saveOrdersChunk($ordersChunk);
}

fclose($ordersResource);
fclose($logResource);

$rows = getTotalOrdersRows();

echo "\n";
echo  $rows . ' Заказов было создано!!!' . PHP_EOL;
echo "Done!!!\n";
exit();


function validateOrderLine($orderLine): array
{
    $order = handleOrder($orderLine);

    $error = match (false) {
        validateOrderStructure($orderLine) => 'Invalid order structure',
        is_int((int)$order[0]) => "Merchant Id is not integer {$order[0]}",
        is_int((int)$order[1]) => "Client Id is not integer {$order[1]}",
        is_string($order[2]) => 'Order commentary is not string',
        merchandiseExists($order[0]) => "Merchandise of order does not exists, id {$order[0]}",
        clientExists($order[1]) => "Client of order does not exists, id {$order[1]}",
        default => null
    };

    return ['order' => $order, 'error' => $error];
}


function handleOrdersValues(array $ordersChunk): string
{
    $str = '';

    foreach ($ordersChunk as $arr) {
        $str .= "({$arr[0]}, {$arr[1]}, '$arr[2]'),";
    }

    return rtrim($str, ",");
}


function saveOrdersChunk(array $ordersChunk): void
{
    global $pdo;

    $sql = sprintf(
        'INSERT INTO orders(item_id, customer_id, comment) VALUES %s',
        handleOrdersValues($ordersChunk)
    );

    $statement = $pdo->prepare($sql);

    $statement->execute();
}


function handleOrder($orderLine): array
{
    return explode(';', $orderLine);
}

function validateOrderStructure($orderLine): bool
{
    $orderData = explode(';', $orderLine);

    if (count($orderData) !== 3) {
        return false;
    } elseif (empty($orderData)) {
        return false;
    }
    return true;
}

function clientExists($customerId): bool
{
    global $pdo;

    $sql = 'select * from clients c where c.id = :customerId';

    $statement = $pdo->prepare($sql);

    $statement->bindValue(':customerId', $customerId, PDO::PARAM_INT);

    try {
        $statement->execute();
    } catch (Exception $exception) {
        return false;
    }

    $customer = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (empty($customer)) {
        return false;
    }
    return true;
}

function merchandiseExists($itemId): bool
{
    global $pdo;

    $sql  = 'select * from merchandise m where m.id = :itemId';

    $statement = $pdo->prepare($sql);

    $statement->bindValue(':itemId', $itemId, PDO::PARAM_INT);

    try {
        $statement->execute();
    } catch (Exception $exception) {
        return false;
    }

    $item = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (empty($item)) {
        return false;
    }
    return true;
}

function logErrors($logResource, $error): void
{
    fwrite($logResource, "$error\n");
    echo "error log $error\n";
}

function getTotalOrdersRows(): int
{
    global $pdo;

    $sql = 'select count(*) from orders';

    $statement = $pdo->prepare($sql);

    $statement->execute();

    $rows = $statement->fetchAll(PDO::FETCH_COLUMN);

    return $rows[0];
}