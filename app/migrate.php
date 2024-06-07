<?php

require_once '../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

$oldProductsFile = '../data/oldProducts.json';
$newProductsFile = '../data/products.json';

$oldProductsData = json_decode(file_get_contents($oldProductsFile), true);

$newProductsData = array_map(function ($oldProduct) {
    return [
        'id' => Uuid::uuid4()->toString(),
        'name' => $oldProduct['name'],
        'amount' => $oldProduct['amount'],
        'createdBy' => $oldProduct['createdBy'],
        'price' => null,
        'expiresAt' => null,
        'createdAt' => $oldProduct['createdAt'],
        'updatedAt' => $oldProduct['updatedAt'],
        'deletedAt' => $oldProduct['deletedAt']
    ];
}, $oldProductsData);

file_put_contents($newProductsFile, json_encode($newProductsData, JSON_PRETTY_PRINT));

echo "Done! Find it at $newProductsFile.\n";
