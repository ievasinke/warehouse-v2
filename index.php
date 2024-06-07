<?php

require_once 'vendor/autoload.php';

use App\WarehouseManager;
use App\UserManager;
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
            ['5', 'Create report'],
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
            $productAmount = (int)readline("Enter the amount: ");
            $productPrice = (float)readline("Enter the price: ");
            $productExpiresInDays = (int)readline("Enter quality expiration days (0 if none): ");
            try {
                $warehouseManager->add(
                    $productName,
                    $productAmount,
                    $user->getName(),
                    $productPrice,
                    $productExpiresInDays
                );
                echo "Product added successfully.\n";
            } catch (Exception $e) {
                echo "Error: {$e->getMessage()}\n";
            }
            break;
        case 2:
            try {
                $warehouseManager->display();
                $choice = (int)readline("Enter product index: ");
                $index = $choice - 1;

                $products = $warehouseManager->load();
                if (!isset($products[$index])) {
                    echo "Invalid product index.\n";
                    break;
                }

                $productAmount = (int)readline("Enter the number of units you want add/(-)remove: ");
                $warehouseManager->updateAmount($products, $index, $productAmount, $user->getName());
            } catch (Exception $e) {
                echo "Error: {$e->getMessage()}\n";
            }
            break;
        case 3:
            try {
                $warehouseManager->display();
                $choice = (int)readline("Enter product index to delete: ");
                $index = $choice - 1;
                $warehouseManager->delete($index, $user);
            } catch (Exception $e) {
                echo "Error: {$e->getMessage()}\n";
            }
            break;
        case 4:
            try {
                $warehouseManager->display();
            } catch (Exception $e) {
                echo "Error: {$e->getMessage()}\n";
            }
            break;
        case 5:
            $warehouseManager->showReport($user->getName());
            break;
        default:
            echo "Invalid action. Please try again.\n";
            break;
    }
}
