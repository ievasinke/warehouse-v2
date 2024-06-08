<?php

namespace App;

use Carbon\Carbon;

class Logger
{
    private string $logFile;

    public function __construct(string $logFile = 'logs/warehouse.log')
    {
        $this->logFile = $logFile;
    }

    public function log(string $entry): void
    {
        file_put_contents(
            $this->logFile,
            Carbon::now('Europe/Riga')->format(WarehouseManager::TIME_FORMAT) . " " . $entry . PHP_EOL,
            FILE_APPEND
        );
    }
}