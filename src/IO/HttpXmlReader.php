<?php

declare(strict_types=1);

namespace SchemaTransformer\IO;

use SchemaTransformer\Interfaces\AbstractDataReader;

class HttpXmlReader implements AbstractDataReader
{
    private array $headers;

    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    public function read(string $path): array|false
    {
        $curl = curl_init($path);

        $headers = array_merge([
            "Accept: application/xml"
        ], $this->headers);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // Disable host verification

        $response = curl_exec($curl);

        if (false === $response) {
            error_log("CURL Error: " . curl_error($curl));
            return false;
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode >= 400) {
            error_log("HTTP Error: " . $httpCode);
            return false;
        }

        return ['content' => $response];
    }
}
