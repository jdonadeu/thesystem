<?php

namespace App\Service;

class FilesystemService
{
    public function saveCsvFile(string $filename, array $data): void
    {
        $fp = fopen($filename, 'w');

        foreach ($data as $match) {
            fputcsv($fp, $match);
        }

        fclose($fp);
    }
}