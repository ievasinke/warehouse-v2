<?php

namespace App;

use Exception;
use Carbon\Carbon;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
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
                $productData->expiresInDays,
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

    public function display(array $additionalRows = []): void
    {
        $products = $this->load();

        $activeProducts = array_filter($products, function (Product $product): bool {
            return $product->getDeletedAt() === null;
        });

        if (empty($activeProducts)) {
            throw new Exception("No products available.");
        }

        $outputTasks = new ConsoleOutput();
        $tableProducts = new Table($outputTasks);
        $tableProducts
            ->setHeaders(['Index', 'Name', 'Amount', 'Created by', 'Price', 'Expires', 'Created at', 'Last Updated']);
        $rows = (array_map(function (int $index, Product $product): array {
            return [
                $index + 1,
                $product->getName(),
                $product->getAmount(),
                $product->getCreatedBy(),
                $product->getPrice(),
                $product->getExpiresInDays()
                    ? $product->getExpiresInDays()->format('m/d/Y')
                    : null,
                $product->getCreatedAt()->format('m/d/Y H:i:s'),
                $product->getUpdatedAt()
                    ? $product->getUpdatedAt()->format('m/d/Y H:i:s')
                    : null,
            ];
        }, array_keys($activeProducts), $activeProducts));

        if (!empty($additionalRows)) {
            $rows = array_merge($rows, $additionalRows);
        }

        $tableProducts->setRows($rows);
        $tableProducts->render();
    }

    public function add(
        string $name,
        int    $amount,
        string $createdBy,
        float  $price,
        int    $expiresInDays = 0): void
    {
        if (empty($name)) {
            throw new Exception("Product name cannot be empty.");
        }
        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero.");
        }
        if (empty($createdBy)) {
            throw new Exception("Created by cannot be empty.");
        }
        if ($price <= 0) {
            throw new Exception("Price must be greater than zero.");
        }

        $expiresAtCarbon = $expiresInDays > 0 ? Carbon::now()->addDays($expiresInDays) : null;
        $products = $this->load();
        $id = Uuid::uuid4()->toString();
        $newProduct = new Product($id, $name, $amount, $createdBy, $price, $expiresAtCarbon);
        $products[] = $newProduct;
        $this->save($products);
        createLogEntry("Product added: $name by $createdBy");
    }

    /**
     * @param Product[] $products
     * @param int $index
     * @param int $amount
     * @param string $user
     * @return void
     */
    public function updateAmount(array $products, int $index, int $amount, string $user): void
    {
        $product = $products[$index];
        $units = $product->getAmount() + $amount;
        var_dump($units);
        if ($units < 0) {
            throw new Exception("Units can't be greater than current amount.");
        }
        $product->setAmount($amount);
        $this->save($products);
        createLogEntry("$user updated product: ID {$product->getId()}, amount changed by $amount units");
    }

    public function delete(int $index, string $user): void
    {
        $products = $this->load();
        $product = $products[$index];
        $product->setDeletedAt(Carbon::now());
        $this->save($products);
        createLogEntry("$user deleted product: ID {$product->getId()}");
    }

    private function createReport(): array
    {
        $products = $this->load();
        $totalAmount = 0;
        $totalSum = 0;
        $alignLeft = new TableCellStyle(['align' => 'right',]);
        foreach ($products as $product) {
            $totalAmount += $product->getAmount();
            $itemSum = (float)number_format($product->getAmount() * $product->getPrice(), 2);
            $totalSum += $itemSum;
        }

        $reportRow = [
            new TableCell('Total units:', ['colspan' => 2, 'style' => $alignLeft]),
            $totalAmount,
            new TableCell('Total:', ['style' => $alignLeft]),
            $totalSum,
            new TableCell('Date:', ['style' => $alignLeft]),
            Carbon::now('Europe/Riga')->format('m/d/Y H:i:s'),
            null
        ];
        return [$reportRow];
    }

    public function showReport($user): void
    {
        try {
            $additionalRows = $this->createReport();
            $this->display($additionalRows);
            createLogEntry("$user created a report");
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
