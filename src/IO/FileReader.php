<?php

declare(strict_types=1);

namespace SchemaTransformer\IO;

use SchemaTransformer\Interfaces\AbstractDataReader;

class FileReader implements AbstractDataReader
{
    public function read(string $path): array|false
    {
        $file = file_get_contents($path);
        if (false === $file) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'xml') {
            return ['content' => $file]; // Return XML as string in content key
        } else {
            return json_decode($file, true);
        }
    }
}
