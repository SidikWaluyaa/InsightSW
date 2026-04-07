<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class GoogleSheetService
{
    /**
     * Fetch and parse CSV data from a Google Sheet URL and GID.
     * URL Format: https://docs.google.com/spreadsheets/d/{ID}/edit#gid={GID}
     */
    public function fetchData(string $url, string $gid, int $skipRows = 0): Collection
    {
        $spreadsheetId = $this->extractSpreadsheetId($url);

        if (!$spreadsheetId) {
            throw new \Exception("Invalid Google Sheets URL format.");
        }

        // Standard CSV Export URL (Better for preserving headers in complex sheets)
        $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";

        $response = Http::timeout(60)->get($csvUrl);

        if ($response->failed()) {
            throw new \Exception("Gagal mengambil data dari Google Sheets. Pastikan akses 'Anyone with the link' sudah aktif.");
        }

        return $this->parseCsv($response->body(), $skipRows);
    }

    /**
     * Extract Spreadsheet ID from the full URL.
     */
    private function extractSpreadsheetId(string $url): ?string
    {
        preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Parse CSV string into an associative collection.
     */
    private function parseCsv(string $csvContent, int $skipRows = 0): Collection
    {
        $lines = explode("\n", $csvContent);
        if (empty($lines)) return collect();

        // Skip decorative rows before headers
        for ($i = 0; $i < $skipRows; $i++) {
            array_shift($lines);
        }

        // Get headers and normalize (lowercase, underscore for spaces/dashes)
        $rawHeaders = str_getcsv(array_shift($lines));
        $counts = [];
        $headers = array_map(function($h) use (&$counts) {
            $h = trim($h, '" ');
            $h = strtolower($h);
            $h = str_replace([' ', '-'], '_', $h);
            
            if (isset($counts[$h])) {
                $counts[$h]++;
                return "{$h}_" . $counts[$h];
            }
            
            $counts[$h] = 1;
            return $h;
        }, $rawHeaders);

        $data = collect();

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $row = str_getcsv($line);
            if (count($row) !== count($headers)) continue;

            $mappedRow = array_combine($headers, $row);
            
            // Cleanup quotes and whitespace from values
            $mappedRow = array_map(fn($v) => trim($v, '" '), $mappedRow);

            $data->push($mappedRow);
        }

        return $data;
    }
}
