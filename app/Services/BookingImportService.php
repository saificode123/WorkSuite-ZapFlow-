<?php

namespace App\Services;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * BookingImportService
 *
 * Parses the confirmed production booking import format:
 *
 * Row 1: "Group Detail"  (header marker)
 * Row 2: GroupNo, <value>
 * Row 3: GroupName, <value>    e.g. "KH-EM2A1AN-0011"
 * Row 4: PassportNo, First Name, Family Name, Birth Date, Gender, Mofa
 * Row 5+: one row per pilgrim:
 *   LB5166012, SYED, ZAFAR HUSSAIN SHAH, 01/01/1950, Male, 0
 *   BS8978321, AMEEN, FARIDA, 01/01/1962, Female, 0
 */
class BookingImportService
{
    /**
     * Parse a raw CSV/text file upload into a structured preview array.
     * This is the PREVIEW step — nothing is written to the DB here.
     *
     * @param  string  $filePath  Absolute path to the uploaded file
     * @return array{group_no: string, group_name: string, passengers: Collection, errors: array}
     */
    public function parseFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['xlsx', 'xls'])) {
            $rows = $this->parseExcel($filePath);
        } else {
            $rows = $this->parseCsv($filePath);
        }

        return $this->extractGroupData($rows);
    }

    /**
     * Parse CSV content from a string (for testing/preview from textarea).
     */
    public function parseContent(string $content): array
    {
        $rows = [];
        $lines = preg_split('/\r\n|\r|\n/', trim($content));

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $rows[] = str_getcsv($line);
        }

        return $this->extractGroupData($rows);
    }

    /**
     * Extract group header + passenger rows from a parsed row array.
     */
    private function extractGroupData(array $rows): array
    {
        $groupNo   = null;
        $groupName = null;
        $passengers = collect();
        $errors    = [];
        $inPassengerSection = false;
        $rowIndex  = 0;

        foreach ($rows as $row) {
            $rowIndex++;
            // Trim all cells
            $row = array_map('trim', $row);

            if (empty(array_filter($row))) {
                continue; // skip blank rows
            }

            $firstCell = strtolower($row[0] ?? '');

            // Header marker row
            if (str_contains($firstCell, 'group detail')) {
                continue;
            }

            // GroupNo row
            if ($firstCell === 'groupno' || $firstCell === 'group no' || $firstCell === 'group_no') {
                $groupNo = $row[1] ?? null;
                continue;
            }

            // GroupName row
            if ($firstCell === 'groupname' || $firstCell === 'group name' || $firstCell === 'group_name') {
                $groupName = $row[1] ?? null;
                continue;
            }

            // Column header row for passengers
            if ($firstCell === 'passportno' || $firstCell === 'passport no' || $firstCell === 'passport_no') {
                $inPassengerSection = true;
                continue;
            }

            // Passenger data rows
            if ($inPassengerSection) {
                $passengerResult = $this->parsePassengerRow($row, $rowIndex);

                if (!empty($passengerResult['errors'])) {
                    $errors = array_merge($errors, $passengerResult['errors']);
                } else {
                    $passengers->push($passengerResult['passenger']);
                }
            }
        }

        if (!$groupName && !$groupNo) {
            $errors[] = 'Could not find GroupName or GroupNo headers. Please check the file format.';
        }

        return [
            'group_no'   => $groupNo ?? '',
            'group_name' => $groupName ?? '',
            'passengers' => $passengers,
            'errors'     => $errors,
            'total'      => $passengers->count(),
        ];
    }

    /**
     * Parse a single passenger row.
     * Expected columns: PassportNo | First Name | Family Name | Birth Date | Gender | Mofa
     */
    private function parsePassengerRow(array $row, int $rowIndex): array
    {
        $errors = [];

        $passportNo  = $row[0] ?? null;
        $firstName   = $row[1] ?? null;
        $familyName  = $row[2] ?? null;
        $birthDate   = $row[3] ?? null;
        $gender      = $row[4] ?? null;
        $mofaStatus  = $row[5] ?? '0';

        // Validation
        if (empty($passportNo)) {
            $errors[] = "Row {$rowIndex}: Passport number is required.";
        }

        if (empty($firstName)) {
            $errors[] = "Row {$rowIndex}: First name is required.";
        }

        if (empty($familyName)) {
            $errors[] = "Row {$rowIndex}: Family name is required.";
        }

        if (!empty($birthDate)) {
            $parsed = $this->parseDateFlexible($birthDate);
            if (!$parsed) {
                $errors[] = "Row {$rowIndex}: Birth date '{$birthDate}' is not a valid date (expected DD/MM/YYYY or YYYY-MM-DD).";
            }
            $birthDate = $parsed ?? $birthDate;
        } else {
            $errors[] = "Row {$rowIndex}: Birth date is required.";
        }

        $genderNorm = $this->normalizeGender($gender);
        if (!$genderNorm) {
            $errors[] = "Row {$rowIndex}: Gender '{$gender}' is not valid. Use 'Male' or 'Female'.";
        }

        if (!empty($errors)) {
            return ['errors' => $errors, 'passenger' => null];
        }

        return [
            'errors' => [],
            'passenger' => [
                'passport_no'  => strtoupper($passportNo),
                'first_name'   => strtoupper(trim($firstName)),
                'family_name'  => strtoupper(trim($familyName)),
                'birth_date'   => $birthDate,
                'gender'       => $genderNorm,
                'mofa_status'  => (string) $mofaStatus,
                'visa_pipeline_status' => 'draft',
            ],
        ];
    }

    /**
     * Parse date in multiple formats (DD/MM/YYYY, MM/DD/YYYY, YYYY-MM-DD).
     */
    private function parseDateFlexible(string $dateStr): ?string
    {
        // Try DD/MM/YYYY first (production format from example)
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $m)) {
            $date = \DateTime::createFromFormat('d/m/Y', $dateStr);
            if ($date && $date->format('d/m/Y') === sprintf('%02d/%02d/%04d', $m[1], $m[2], $m[3])) {
                return $date->format('Y-m-d');
            }
        }

        // Try YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        return null;
    }

    private function normalizeGender(?string $gender): ?string
    {
        $gender = strtolower(trim($gender ?? ''));
        if (in_array($gender, ['male', 'm'])) return 'Male';
        if (in_array($gender, ['female', 'f'])) return 'Female';
        return null;
    }

    private function parseCsv(string $filePath): array
    {
        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function parseExcel(string $filePath): array
    {
        // Uses maatwebsite/excel which is already installed
        $data = \Maatwebsite\Excel\Facades\Excel::toArray([], $filePath);
        return $data[0] ?? [];
    }
}
