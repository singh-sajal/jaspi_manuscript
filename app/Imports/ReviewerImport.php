<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReviewerImport
{
    /**
     * Reads an uploaded Excel/CSV file and returns a unique collection
     * of lowercase email addresses found in a column headed "email".
     */
    public function import(string $filePath): Collection
    {
        $spreadsheet = IOFactory::load($filePath);
        $rows        = $spreadsheet->getActiveSheet()->toArray();

        if (empty($rows)) {
            return collect();
        }

        // First row is the header — find the "email" column index
        $headers   = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0]);
        $emailCol  = array_search('email', $headers);

        if ($emailCol === false) {
            return collect(); // no "email" column found
        }

        return collect(array_slice($rows, 1)) // skip header row
            ->map(fn($row) => strtolower(trim((string) ($row[$emailCol] ?? ''))))
            ->filter()          // drop empty cells
            ->unique()
            ->values();
    }
}
