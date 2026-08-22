<?php

namespace App\Services;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

final class HgTripXlsxReader
{
    private const MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * Read a simple, tabular XLSX workbook without changing the source file.
     * The returned rows are keyed by the first row's header names.
     *
     * @return array<string, list<array<string, string>>>
     */
    public function read(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Cannot open XLSX file: {$path}");
        }

        $workbook = $this->xml($zip, 'xl/workbook.xml');
        $relationships = $this->xml($zip, 'xl/_rels/workbook.xml.rels');
        $sharedStrings = $this->sharedStrings($zip);
        $relationTargets = [];

        foreach ($relationships->Relationship as $relation) {
            $attributes = $relation->attributes();
            $relationTargets[(string) $attributes['Id']] = (string) $attributes['Target'];
        }

        $sheets = [];
        $workbookSheets = $workbook->children(self::MAIN_NS)->sheets->sheet ?? [];

        foreach ($workbookSheets as $sheet) {
            $attributes = $sheet->attributes();
            $relationAttributes = $sheet->attributes(self::REL_NS);
            $relationId = (string) ($relationAttributes['id'] ?? '');
            $target = $relationTargets[$relationId] ?? null;

            if (!$target) {
                continue;
            }

            $target = ltrim($target, '/');
            $target = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            $worksheet = $this->xml($zip, $target);
            $rows = $worksheet->children(self::MAIN_NS)->sheetData->row ?? [];
            $sheets[(string) $attributes['name']] = $this->tabularRows($rows, $sharedStrings);
        }

        $zip->close();

        return $sheets;
    }

    /**
     * Read worksheet rows without assuming that the first row contains a table
     * header. This is used for supplier schedule workbooks, where a title and
     * a few blank rows commonly appear before the actual column headings.
     *
     * @return array<string, list<array{row: int, cells: array<int, string>}>>
     */
    public function readRows(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Cannot open XLSX file: {$path}");
        }

        try {
            $workbook = $this->xml($zip, 'xl/workbook.xml');
            $relationships = $this->xml($zip, 'xl/_rels/workbook.xml.rels');
            $sharedStrings = $this->sharedStrings($zip);
            $relationTargets = [];

            foreach ($relationships->Relationship as $relation) {
                $attributes = $relation->attributes();
                $relationTargets[(string) $attributes['Id']] = (string) $attributes['Target'];
            }

            $sheets = [];
            $workbookSheets = $workbook->children(self::MAIN_NS)->sheets->sheet ?? [];

            foreach ($workbookSheets as $sheet) {
                $attributes = $sheet->attributes();
                $relationAttributes = $sheet->attributes(self::REL_NS);
                $relationId = (string) ($relationAttributes['id'] ?? '');
                $target = $relationTargets[$relationId] ?? null;

                if (! $target) {
                    continue;
                }

                $target = ltrim($target, '/');
                $target = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
                $worksheet = $this->xml($zip, $target);
                $rows = $worksheet->children(self::MAIN_NS)->sheetData->row ?? [];
                $sheets[(string) $attributes['name']] = $this->worksheetRows($rows, $sharedStrings);
            }

            return $sheets;
        } finally {
            $zip->close();
        }
    }

    private function xml(ZipArchive $zip, string $name): SimpleXMLElement
    {
        $contents = $zip->getFromName($name);

        if ($contents === false) {
            throw new RuntimeException("Missing XLSX entry: {$name}");
        }

        $xml = simplexml_load_string($contents);

        if (!$xml instanceof SimpleXMLElement) {
            throw new RuntimeException("Invalid XML in XLSX entry: {$name}");
        }

        return $xml;
    }

    /** @return list<string> */
    private function sharedStrings(ZipArchive $zip): array
    {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }

        $xml = $this->xml($zip, 'xl/sharedStrings.xml');
        $values = [];

        foreach ($xml->children(self::MAIN_NS)->si as $item) {
            $textNodes = $item->xpath('.//*[local-name()="t"]') ?: [];
            $values[] = implode('', array_map(static fn (SimpleXMLElement $node): string => (string) $node, $textNodes));
        }

        return $values;
    }

    /** @param iterable<SimpleXMLElement> $rows @return list<array<string, string>> */
    private function tabularRows(iterable $rows, array $sharedStrings): array
    {
        $header = null;
        $result = [];

        foreach ($rows as $row) {
            $cells = [];

            foreach ($row->children(self::MAIN_NS)->c as $cell) {
                $attributes = $cell->attributes();
                $reference = (string) ($attributes['r'] ?? '');
                $column = $this->columnIndex($reference);
                $cells[$column] = $this->cellValue($cell, $sharedStrings);
            }

            if ($header === null) {
                $header = $cells;
                continue;
            }

            $record = [];
            foreach ($header as $column => $name) {
                $name = trim((string) $name);

                if ($name !== '') {
                    $record[$name] = (string) ($cells[$column] ?? '');
                }
            }

            if (array_filter($record, static fn (string $value): bool => trim($value) !== '')) {
                $result[] = $record;
            }
        }

        return $result;
    }

    /** @param iterable<SimpleXMLElement> $rows @return list<array{row: int, cells: array<int, string>}> */
    private function worksheetRows(iterable $rows, array $sharedStrings): array
    {
        $result = [];

        foreach ($rows as $row) {
            $rowAttributes = $row->attributes();
            $cells = [];

            foreach ($row->children(self::MAIN_NS)->c as $cell) {
                $attributes = $cell->attributes();
                $cells[$this->columnIndex((string) ($attributes['r'] ?? ''))] = $this->cellValue($cell, $sharedStrings);
            }

            $result[] = [
                'row' => (int) ($rowAttributes['r'] ?? 0),
                'cells' => $cells,
            ];
        }

        return $result;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $attributes = $cell->attributes();
        $type = (string) ($attributes['t'] ?? '');
        $main = $cell->children(self::MAIN_NS);

        if ($type === 's') {
            return $sharedStrings[(int) ($main->v ?? 0)] ?? '';
        }

        if ($type === 'inlineStr') {
            $textNodes = $main->is->xpath('.//*[local-name()="t"]') ?: [];
            return implode('', array_map(static fn (SimpleXMLElement $node): string => (string) $node, $textNodes));
        }

        return (string) ($main->v ?? '');
    }

    private function columnIndex(string $reference): int
    {
        $letters = preg_replace('/[^A-Z].*$/', '', strtoupper($reference)) ?? '';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }
}
