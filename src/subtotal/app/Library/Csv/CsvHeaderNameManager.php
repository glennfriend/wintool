<?php
namespace App\Library\Csv;

/**
 * 淨化 csv 的 headers
 */
class CsvHeaderNameManager
{

    public static function convert(array $headers)
    {
        $convertNames = [];
        foreach ($headers as $index => $value) {
            $convertNames[$index] = static::normalNameCase($value);
        }

        $convertNames = static::convertEmptyNames($convertNames);
        $convertNames = static::convertRepeatNames($convertNames);
        return $convertNames;
    }

    // --------------------------------------------------------------------------------
    //
    // --------------------------------------------------------------------------------

    /**
     * 將 header name 轉為乾淨的字串
     */
    private static function normalNameCase($value): string
    {
        $value = str_replace(array('-',' '), '_', $value);
        $value = preg_replace("/[^a-zA-Z0-9_]+/", "", $value);
        $value = preg_replace("/[_]+/", "_", $value);
        $value = strtolower(trim($value));
        return $value;
    }

    /**
     * 處理空值的名稱
     */
    private static function convertEmptyNames(array $rows): array
    {
        $result = [];
        foreach ($rows as $index => $name) {
            if (!$name) {
                $name = 'name_' . $index;
            }
            array_push($result, $name);
        }
        return $result;
    }

    /**
     * 處理重覆名稱問題
     */
    private static function convertRepeatNames(array $rows): array
    {
        $allowRow = [];
        foreach ($rows as $index => $name) {
            if (in_array($name, $allowRow)) {
                $name .= '_repeat_' . $index;
            }
            array_push($allowRow, $name);
        }
        return $allowRow;
    }

}

