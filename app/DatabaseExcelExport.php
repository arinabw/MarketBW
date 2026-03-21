<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Выгрузка SQLite в XML Spreadsheet 2003 — Excel открывает файл как таблицу (.xls).
 * Листы = таблицы БД; колонка password_hash в users маскируется.
 */
final class DatabaseExcelExport
{
    /**
     * @return resource
     */
    public static function openSpreadsheetXmlStream(Database $db)
    {
        $pdo = $db->pdo();
        $tables = $db->listSqliteTables();
        $h = fopen('php://temp', 'r+');
        if ($h === false) {
            throw new \RuntimeException('Cannot open memory stream');
        }
        fwrite($h, "\xEF\xBB\xBF");
        fwrite($h, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        fwrite($h, '<?mso-application progid="Excel.Sheet"?>' . "\n");
        fwrite($h, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ');
        fwrite($h, 'xmlns:o="urn:schemas-microsoft-com:office:office" ');
        fwrite($h, 'xmlns:x="urn:schemas-microsoft-com:office:excel" ');
        fwrite($h, 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ');
        fwrite($h, 'xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n");
        foreach ($tables as $table) {
            self::writeWorksheet($h, $pdo, $table);
        }
        fwrite($h, '</Workbook>');
        rewind($h);

        return $h;
    }

    /**
     * @param resource $h
     */
    private static function writeWorksheet($h, PDO $pdo, string $table): void
    {
        $q = $pdo->query('SELECT * FROM ' . Database::quoteSqliteIdentifier($table));
        $sheetTitle = self::excelSheetTitle($table);
        fwrite($h, '<Worksheet ss:Name="' . self::xmlAttr($sheetTitle) . '"><Table>' . "\n");
        if (!$q) {
            fwrite($h, '<Row><Cell><Data ss:Type="String">(ошибка чтения)</Data></Cell></Row>');
            fwrite($h, '</Table></Worksheet>' . "\n");

            return;
        }
        $first = $q->fetch(PDO::FETCH_ASSOC);
        if ($first === false) {
            fwrite($h, '<Row><Cell><Data ss:Type="String">(нет строк)</Data></Cell></Row>');
            fwrite($h, '</Table></Worksheet>' . "\n");

            return;
        }
        if ($table === 'users') {
            $first['password_hash'] = '*** (не выгружается)';
        }
        $headers = array_keys($first);
        fwrite($h, '<Row>');
        foreach ($headers as $col) {
            fwrite($h, '<Cell><Data ss:Type="String">' . self::xmlText((string) $col) . '</Data></Cell>');
        }
        fwrite($h, '</Row>' . "\n");
        self::writeDataRowXml($h, $headers, $first);
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            if ($table === 'users') {
                $row['password_hash'] = '*** (не выгружается)';
            }
            self::writeDataRowXml($h, $headers, $row);
        }
        fwrite($h, '</Table></Worksheet>' . "\n");
    }

    /**
     * @param resource $h
     * @param list<string> $headers
     * @param array<string, mixed> $row
     */
    private static function writeDataRowXml($h, array $headers, array $row): void
    {
        fwrite($h, '<Row>');
        foreach ($headers as $key) {
            $val = $row[$key] ?? '';
            if (!is_scalar($val) && $val !== null) {
                try {
                    $s = json_encode($val, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    $s = '';
                }
            } else {
                $s = $val === null ? '' : (string) $val;
            }
            fwrite($h, '<Cell><Data ss:Type="String">' . self::xmlText($s) . '</Data></Cell>');
        }
        fwrite($h, '</Row>' . "\n");
    }

    private static function xmlAttr(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private static function xmlText(string $s): string
    {
        return htmlspecialchars($s, ENT_NOQUOTES | ENT_XML1, 'UTF-8');
    }

    public static function excelSheetTitle(string $name): string
    {
        $s = preg_replace('/[:\\\\\\/\\?\\*\\[\\]]/u', '-', $name) ?? $name;
        if (function_exists('mb_substr')) {
            $s = mb_substr($s, 0, 31);
        } else {
            $s = substr($s, 0, 31);
        }

        return $s !== '' ? $s : 'Sheet';
    }
}
