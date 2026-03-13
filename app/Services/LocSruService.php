<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LocSruService
{
    /**
     * Voyager SRU (Z39.50-backed)
     */
    protected string $endpoint = 'http://z3950.loc.gov:7090/voyager';

    public function search(?string $isbn = null, ?string $title = null): ?array
    {
        if ($isbn) {
            $isbn = preg_replace('/[^0-9Xx]/', '', $isbn);
            // Bib-1 attribute 1=7 = ISBN
            $query = '@attr 1=7 ' . $isbn;
        } elseif ($title) {
            // Bib-1 attribute 1=4 = Title
            $query = '@attr 1=4 "' . addslashes($title) . '"';
        } else {
            return null;
        }

        $response = Http::timeout(20)->get($this->endpoint, [
            'operation'      => 'searchRetrieve',
            'version'        => '1.1',
            'query'          => $query,
            'recordSchema'   => 'marcxml',
            'maximumRecords' => 1,
        ]);

        if (!$response->successful()) {
            return null;
        }

        return $this->parseMarcXml($response->body());
    }

    protected function parseMarcXml(string $xml): ?array
    {
        libxml_use_internal_errors(true);

        $xmlObj = simplexml_load_string($xml);
        if (!$xmlObj) {
            return null;
        }

        $records = $xmlObj->xpath('//*[local-name()="record"]');
        if (!$records || !isset($records[0])) {
            return null;
        }

        $record = $records[0];

        return [
            'title'       => $this->getMarcSubfield($record, '245', 'a'),
            'author'      => $this->getMarcSubfield($record, '100', 'a')
                              ?? $this->getMarcSubfield($record, '110', 'a')
                              ?? $this->getMarcSubfield($record, '700', 'a'),
            'publisher'   => $this->getMarcSubfield($record, '260', 'b')
                              ?? $this->getMarcSubfield($record, '264', 'b'),
            'year'        => $this->getMarcSubfield($record, '260', 'c')
                              ?? $this->getMarcSubfield($record, '264', 'c'),
            'isbn'        => $this->getMarcSubfield($record, '020', 'a'),
            'call_number' => $this->getMarcSubfield($record, '050', 'a'),
        ];
    }

    protected function getMarcSubfield($record, string $tag, string $code): ?string
    {
        $fields = $record->xpath('.//*[local-name()="datafield"]');
        if (!$fields) return null;

        foreach ($fields as $field) {
            if ((string) $field['tag'] === $tag) {
                foreach ($field->xpath('.//*[local-name()="subfield"]') as $sub) {
                    if ((string) $sub['code'] === $code) {
                        return trim((string) $sub);
                    }
                }
            }
        }

        return null;
    }
}
