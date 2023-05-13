<?php
#!/usr/bin/php -q
use App\Utility\FileDetectEncoding;
use League\Csv\Reader;
use League\Csv\Writer;

//
$basePath = dirname(__DIR__);
require_once $basePath . '/core/bootstrap.php';
initialize();
perform();
exit;

// --------------------------------------------------------------------------------
//
// --------------------------------------------------------------------------------

/**
 * @throws \League\Csv\CannotInsertRecord
 */
function perform(): void
{
    list($originHeaders, $rows) = getCsvContents();
    if (!$rows) {
        echo "沒有資料";
        exit;
    }

    $key1 = '時數(給付)';       // 第一組時間 的 欄位名稱
    $key2 = '時數(已使用)';     // 第二組時間 的 欄位名稱
    $key3 = '剩餘時數';         // 第三組時間 的 欄位名稱
    $nameKey = '員工代號';      // 以該欄位做為 user 分類名稱

    $previous_user = '';
    $allHeaders = $originHeaders;
    // new_1 -> subtotal_時數(給付)
    // new_2 -> subtotal_時數(已使用)
    // new_3 -> subtotal_剩餘時數
    array_push($allHeaders, 'new_1', 'new_2', 'new_3');

    foreach ($rows as $index => $row) {

        if (!isset($row[$nameKey])) {
            echo 'Error: 解析出來的資料有問題, 請檢查 csv 欄位標題 是否有異動';
            exit;
        }

        $row['new_1'] = '';
        $row['new_2'] = '';
        $row['new_3'] = '';
        $current_user = isset($row[$nameKey]) ? $row[$nameKey] : null;
        if ($previous_user === $current_user) {

            $previousIndex = $index - 1;

            $previousTime = $rows[$previousIndex]['new_1'];
            if (isset($row[$key1])) {
                $row['new_1'] = calculateTimePlus($row[$key1] ?? '', $previousTime);
            }
            $rows[$previousIndex]['new_1'] = '';

            $previousTime = $rows[$previousIndex]['new_2'];
            if (isset($row[$key2])) {
                $row['new_2'] = calculateTimePlus($row[$key2] ?? '', $previousTime);
            }
            $rows[$previousIndex]['new_2'] = '';

            $previousTime = $rows[$previousIndex]['new_3'];
            if (isset($row[$key3])) {
                $row['new_3'] = calculateTimePlus($row[$key3] ?? '', $previousTime);
            }
            $rows[$previousIndex]['new_3'] = '';

        } else {
            if (isset($row[$key1]) && isset($row[$key2]) && isset($row[$key3])) {
                $row['new_1'] = calculateTimePlus($row[$key1]);
                $row['new_2'] = calculateTimePlus($row[$key2]);
                $row['new_3'] = calculateTimePlus($row[$key3]);
            }
        }

        $rows[$index] = $row;
        $previous_user = $current_user;
    }

    $outputHeaders = [
        '員工代號',
        '員工姓名',
        '時數(給付)',
        '時數(已使用)',
        '剩餘時數',
        '部門名稱',
        'new_1',
        'new_2',
        'new_3',
    ];
    downloadCsv($rows, $outputHeaders);
}

// --------------------------------------------------------------------------------
//
// --------------------------------------------------------------------------------

function intersect_values(array $row1, array $row2): array
{
    return array_values(array_intersect($row1, $row2));
}

function filterRows(array $originRows, array $allowKeys): array
{
    $rows = [];
    foreach ($originRows as $index => $originRow) {
        $rows[] = array_intersect_key($originRow, array_flip($allowKeys));
    }

    return $rows;
}

/**
 * 對 天數 及 時數 做計算
 * 時數過 8 則進位到 天數
 * 傳入格式為 日:時:分
 *
 * @param string $timeString format dd:hh:mm
 * @param string $plusTimeString format dd:hh:mm
 * @return string
 */
function calculateTimePlus(string $timeString, $plusTimeString = '')
{
    $parseTimeFunc = function (string $myTime) {
        if (!$myTime) {
            $myTime = '';
        }
        preg_match_all("/([0-9]{2}):([0-9]{2}):([0-9]{2})/is", $myTime, $matches);
        return $matches;
    };

    $times = $parseTimeFunc($timeString);
    //print_r($timeString);
    //print_r($times);
    //echo "<br>\n";
    $day = isset($times[1][0]) ? (int)$times[1][0] : 0;
    $hour = isset($times[2][0]) ? (int)$times[2][0] : 0;
    $minute = isset($times[3][0]) ? (int)$times[3][0] : 0;

    // 如果要相加
    if ($plusTimeString) {
        $plusTimes = $parseTimeFunc($plusTimeString);
        $plusDay = (int)$plusTimes[1][0];
        $plusHoure = (int)$plusTimes[2][0];
        $plusMinute = (int)$plusTimes[3][0];

        $day += $plusDay;
        $hour += $plusHoure;
        $minute += $plusMinute;

        // 將 小時 進位到 天 的值
        $hourAddNumber = floor($hour / 8);
        if ($hourAddNumber) {
            $day += $hourAddNumber;
            $hour = $hour % 8;  // 進位之後, 剩下的小時數
        }
    }

    $format = sprintf('%02s:%02s:%02s', $day, $hour, $minute);
    return $format;
}

/**
 *
 */
function getCsvContents(): array
{
    $varPath = getProjectPath('/var');
    $fileRule = "{$varPath}/*.[cC][sS][vV]";
    $files = glob($fileRule, GLOB_ERR);
    $fileCount = count($files);
    if ($fileCount < 1) {
        echo "{$varPath} 目錄中找不到 csv 檔案";
        exit;
    } elseif ($fileCount > 1) {
        echo "{$varPath} 目錄有多個 csv 檔案, 只保留一個檔案";
        exit;
    }

    $csvFile = $files[0];
    validateFile($csvFile);
    return parseCsv($csvFile);
}

function validateFile(string $file): void
{
    if (!file_exists($file)) {
        die('Error: 檔案不存在!');
    }

    $mimeType = mime_content_type($file);
    switch ($mimeType) {
        case 'text/plain':
        case 'text/csv':
        case 'application/csv':
            // safe
            break;
        default:
            die("Error: 檔案格式不正確 - {$mimeType}");
    }

}

/**
 * @throws \League\Csv\InvalidArgument
 * @throws \League\Csv\UnableToProcessCsv
 * @throws \League\Csv\UnavailableFeature
 */
function parseCsv(string $file): array
{
    $reader = Reader::createFromPath($file);
    $detectEncoding = FileDetectEncoding::detect($file);
    $reader->setHeaderOffset(0);    // csv 有 header, 並且在第一行
    if ($detectEncoding && $detectEncoding !== 'UTF-8') {
        $reader->addStreamFilter("convert.iconv.{$detectEncoding}/UTF-8");
    }

    /*
        try {
            $originHeaders = $reader->fetchOne();
            pr($originHeaders);
        } catch (Exception $exception) {
            echo '讀取 csv 檔案錯誤, 有可能是這個 csv 檔案現在正被被開啟, 請先關閉這個檔案.';
            echo "<br>\n";
            throw $exception;
        }
    */

    foreach ($reader as $row) {
        $rows[] = $row;
    }

    return [$reader->getHeader(), $rows];
}

/**
 * @throws \League\Csv\CannotInsertRecord
 */
function downloadCsv(array $rows, array $headers): void
{
    $writer = factoryWriter($rows, $headers);

    // debug only
    //echo $writer->toString(); exit;


    // $outputPath = getProjectPath('/var/output.csv');
    $outputName = 'output_' . date('Y-m-d') . '.csv';
    // $csv->setOutputBOM(Reader::BOM_UTF8);
    $writer->output($outputName);
}

/**
 * @throws \League\Csv\CannotInsertRecord
 */
function factoryWriter(array $rows, array $headers): Writer
{
    $writer = Writer::createFromFileObject(new SplTempFileObject());
    $writer->insertOne($headers);
    foreach ($rows as $row) {
        $filteredRow = [];
        foreach ($headers as $header) {
            $filteredRow[] = $row[$header] ?? '';
        }
        $writer->insertOne($filteredRow);
    }

    return $writer;
}