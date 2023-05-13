<?php

namespace App\Utility;

class FileDetectEncoding
{
    public static function detect(string $file, array $scope = []): ?string
    {
        if (!$scope) {
            $scope = ["BIG-5", "UTF-8"];
        }
        $contents = file_get_contents($file);
        return mb_detect_encoding($contents, $scope, true);
    }
}
