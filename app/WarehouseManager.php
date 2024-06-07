<?php

namespace App;

use Exception;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;
use Ramsey\Uuid\Uuid;

class WarehouseManager
{
    private string $productFile;

    public function __construct($productFile = 'data/products.json')
    {
        $this->productFile = $productFile;
    }

    public function load(): array
    {
        $products = [];
        $productsData = json_decode(file_get_contents($this->productFile));
        if ($productsData === null) {
            return $products;
        }

        foreach ($productsData as $productData) {
            $products[] = new Product(
                $productData->id,
                $productData->name,
                $productData->amount,
                $productData->createdBy,
                $productData->price,
                $productData->expiresAt,
                $productData->createdAt,
                $productData->updatedAt,
                $productData->deletedAt
            );
        }
        return $products;
    }

    public function save(array $products): void
    {
        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = $product->jsonSerialize();
        }
        file_put_contents($this->productFile, json_encode($productsData, JSON_PRETTY_PRINT));
    }

    public function display(): void
    {
        $products = $this->load();

        if (empty($products)) {
            throw new Exception("No products available.");
        }
        $activeProducts = array_filter($products, function (Product $product): bool {
            return $product->getDeletedAt() === null;
        });

        if (empty($activeProducts)) {
            echo "No active products available.\n";
            return;
        }

        $outputTasks = new ConsoleOutput();
        $tableProducts = new Table($outputTasks);
        $tableProducts
            ->setHeaders(['Index', 'Name', 'Amount', 'Created by', 'Price', 'Expires', 'Created at', 'Last Updated'])
            ->setRows(array_map(function (int $index, Product $product): array {
                return [
                    $index + 1,
                    $product->getName(),
                    $product->getAmount(),
                    $product->getCreatedBy(),
                    $product->getPrice(),
                    $product->getExpiresAt()
                        ? $product->getExpiresAt()->now('Europe/Riga')->format('m/d/Y H:i:s')
                        : null,
                    $product->getCreatedAt()->now('Europe/Riga')->format('m/d/Y H:i:s'),
                    $product->getUpdatedAt()
                        ? $product->getUpdatedAt()->now('Europe/Riga')->format('m/d/Y H:i:s')
                        : null,
                ];
            }, array_keys($activeProducts), $activeProducts));
        $tableProducts->render();
    }

    public function add(
        string $name,
        int    $amount,
        string $createdBy,
        float  $price): void
    {
        $products = $this->load();
        $id = Uuid::uuid4();
        $newProduct = new Product($id, $name, $amount, $createdBy, $price);
        $products[] = $newProduct;
        $this->save($products);
        createLogEntry("Product added: $name by $createdBy");
    }

    public function updateAmount(array $products, int $index, int $amount, string $user): void
    {
        $product = $products[$index];
        $product->setAmount($amount);
        createLogEntry("$user updated product: ID {$product->getId()}, amount changed by $amount units");
    }

    public function delete(int $index, string $user): void
    {
        $products = $this->load();
        $product = $products[$index];
        $product->setDeletedAt(Carbon::now());
        $this->save($products);
        createLogEntry("$user deleted product: ID {$product->getId()}");
        echo "{$product->getName()} deleted successfully.\n";
    }

    public function createReport($user): void
    {
        $products = $this->load();
        $this->display();
        $totalAmount = 0;
        $totalSum = 0;
        foreach ($products as $product) {
            $totalAmount += $product->getAmount();
            $itemSum = (float)number_format($product->getAmount() * $product->getPrice(), 2);
            $totalSum += $itemSum;
        }
        createLogEntry("$user created a report");
        echo "Total amount in a warehouse (units): $totalAmount\n";
        echo "Total sum of goods (euros):          $totalSum\n";
    }
}
