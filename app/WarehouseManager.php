<?php

namespace App;

use Exception;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class WarehouseManager
{
    private string $productFile;

    public function __construct($productFile = 'data/products.json')
    {
        $this->productFile = $productFile;
    }

    /** @return Product[] */
    public function loadProducts(): array
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

    public function saveProducts(array $products): void
    {
        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = $product->jsonSerialize();
        }
        file_put_contents($this->productFile, json_encode($productsData, JSON_PRETTY_PRINT));
    }

    public function displayProducts(): void
    {
        $products = $this->loadProducts();

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
}