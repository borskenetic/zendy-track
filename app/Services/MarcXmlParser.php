<?php

namespace App\Services;

use SimpleXMLElement;

class MarcXmlParser
{
    public function parse(string $xml)
    {
        $xml = new SimpleXMLElement($xml);

        // First result only
        $item = $xml->results->result[0] ?? null;

        if (!$item) {
            return null;
        }

        return [
            'title'       => (string) ($item->title ?? ''),
            'author'      => (string) ($item->contributor->name ?? ''),
            'publisher'   => (string) ($item->publisher ?? ''),
            'year'        => (string) ($item->date ?? ''),
            'isbn'        => $this->extractIsbn($item),
            'subjects'    => $this->extractSubjects($item),
            'call_number' => (string) ($item->classification->lc ?? ''),
            'source'      => 'Library of Congress',
        ];
    }

    protected function extractIsbn($item): ?string
    {
        if (!isset($item->isbn)) {
            return null;
        }

        foreach ($item->isbn as $isbn) {
            return (string) $isbn;
        }

        return null;
    }

    protected function extractSubjects($item): array
    {
        $subjects = [];

        if (isset($item->subject)) {
            foreach ($item->subject as $subject) {
                $subjects[] = (string) $subject;
            }
        }

        return $subjects;
    }
}
