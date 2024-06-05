<?php

require_once 'vendor/autoload.php';

use App\WarehouseManager;
use App\UserManager;
use App\Product;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

function createLogEntry(string $entry): void
{
    file_put_contents(
        'logs/warehouse.log',
        Carbon::now('Europe/Riga')->format('m/d/Y H:i:s') . " " . $entry . PHP_EOL,
        FILE_APPEND
    );
}

function addProducts(
    WarehouseManager $warehouseManager,
    string           $name,
    string           $description,
    int              $amount,
    string           $createdBy): void
{
    $products = $warehouseManager->loadProducts();
    $id = count($products) + 1;
    $newProduct = new Product($id, $name, $description, $amount, $createdBy);
    $products[] = $newProduct;
    $warehouseManager->saveProducts($products);
    createLogEntry("Product added: $name by $createdBy");
}

function updateProductAmount(WarehouseManager $warehouseManager, int $id, int $amount, string $user): void
{
    $products = $warehouseManager->loadProducts();
    foreach ($products as $product) {
        if ($product->getId() === $id) {
            $product->setAmount($amount);
            $warehouseManager->saveProducts($products);
            createLogEntry("$user updated product: ID $id, amount changed by $amount units");
            return;
        }
    }
    echo "Product not found\n";
}

function deleteProduct(WarehouseManager $warehouseManager, int $id, $user): void
{
    $products = $warehouseManager->loadProducts();
    $deletedProduct = null;

    foreach ($products as $product) {
        if ($product->getId() === $id) {
            $product->setDeletedAt(Carbon::now());
            $deletedProduct = $product;
            break;
        }
    }

    if ($deletedProduct !== null) {
        $warehouseManager->saveProducts($products);
        createLogEntry("$user deleted product: ID $id");
        echo "Product deleted successfully.\n";
    } else {
        echo "Product not found.\n";
    }
}

echo "Welcome to the WareHouse app!\n";
$accessCode = (string)readline("Enter your access code: ");

if (strlen($accessCode) !== 4) {
    exit("Invalid access code. Please try again.\n");
}

$userManager = new UserManager();
$user = $userManager->findUserByAccessCode($accessCode);
if ($user === null) {
    exit("No user found.");
}
echo "Welcome $user!\n";

$warehouseManager = new WarehouseManager();

while (true) {
    $outputTasks = new ConsoleOutput();
    $tableActivities = new Table($outputTasks);
    $tableActivities
        ->setHeaders(['Index', 'Action'])
        ->setRows([
            ['1', 'Create'],
            ['2', 'Change amount'],
            ['3', 'Delete'],
            ['4', 'Display'],
            ['0', 'Exit'],
        ])
        ->render();
    $action = (int)readline("Enter the index of the action: ");

    if ($action === 0) {
        break;
    }

    switch ($action) {
        case 1:
            $productName = (string)readline("Enter the name of product: ");
            $productDescription = (string)readline("Enter the description (optional): ");
            $productAmount = (int)readline("Enter the amount: ");
            addProducts($warehouseManager, $productName, $productDescription, $productAmount, $user->getName());
            break;
        case 2:
            try {
                $warehouseManager->displayProducts();
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                break;
            }
            $id = (int)readline("Enter product ID: ");
            $productAmount = (int)readline("Enter the number of units you want add/(-)remove: ");
            updateProductAmount($warehouseManager, $id, $productAmount, $user->getName());
            break;
        case 3:
            try {
                $warehouseManager->displayProducts();
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                break;
            }
            $id = (int)readline("Enter product ID to delete: ");
            deleteProduct($warehouseManager, $id, $user);
            break;
        case 4:
            try {
                $warehouseManager->displayProducts();
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
            break;
        default:
            echo "Invalid action. Please try again.\n";
            break;
    }
}
