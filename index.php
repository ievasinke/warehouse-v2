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
            $warehouseManager->add($productName, $productDescription, $productAmount, $user->getName());
            break;
        case 2:
            try {
                $warehouseManager->display();
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                break;
            }
            $id = (int)readline("Enter product ID: ");
            $productAmount = (int)readline("Enter the number of units you want add/(-)remove: ");
            $warehouseManager->updateAmount($id, $productAmount, $user->getName());
            break;
        case 3:
            try {
                $warehouseManager->display();
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
                break;
            }
            $id = (int)readline("Enter product ID to delete: ");
            $warehouseManager->delete($id, $user);
            break;
        case 4:
            try {
                $warehouseManager->display();
            } catch (Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
            break;
        default:
            echo "Invalid action. Please try again.\n";
            break;
    }
}
