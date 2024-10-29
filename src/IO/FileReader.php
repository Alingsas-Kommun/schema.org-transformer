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

        // Check file extension
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'xml') {
            // For XML files
            return ['content' => $file]; // Return XML as string in content key
        } else {
            // For JSON files (default behavior)
            return json_decode($file, true);
        }
    }
}
