<?php

namespace App;

use Exception;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

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
                $productData->description,
                $productData->amount,
                $productData->createdBy,
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
            ->setHeaders(['Id', 'Name', 'Description', 'Amount', 'Created By', 'Created At', 'Updated At'])
            ->setRows(array_map(function (Product $product): array {
                return [
                    $product->getId(),
                    $product->getName(),
                    $product->getDescription(),
                    $product->getAmount(),
                    $product->getCreatedBy(),
                    $product->getCreatedAt()->now('Europe/Riga')->format('m/d/Y H:i:s'),
                    $product->getUpdatedAt()
                        ? $product->getUpdatedAt()->now('Europe/Riga')->format('m/d/Y H:i:s')
                        : null,
                ];
            }, $activeProducts));
        $tableProducts->render();
    }

    public function add(
        string $name,
        string $description,
        int    $amount,
        string $createdBy): void
    {
        $products = $this->load();
        $id = count($products) + 1;
        $newProduct = new Product($id, $name, $description, $amount, $createdBy);
        $products[] = $newProduct;
        $this->save($products);
        createLogEntry("Product added: $name by $createdBy");
    }

    public function updateAmount(int $id, int $amount, string $user): void
    {
        $products = $this->load();
        foreach ($products as $product) {
            if ($product->getId() === $id) {
                $product->setAmount($amount);
                $this->save($products);
                createLogEntry("$user updated product: ID $id, amount changed by $amount units");
                return;
            }
        }
        echo "Product not found\n";
    }

    public function delete(int $id, string $user): void
    {
        $products = $this->load();
        $deletedProduct = null;

        foreach ($products as $product) {
            if ($product->getId() === $id) {
                $product->setDeletedAt(Carbon::now());
                $deletedProduct = $product;
                break;
            }
        }

        if ($deletedProduct !== null) {
            $this->save($products);
            createLogEntry("$user deleted product: ID $id");
            echo "Product deleted successfully.\n";
        } else {
            echo "Product not found.\n";
        }
    }
}