<?php
#!/usr/bin/php -q
use League\Csv\Reader;
use League\Csv\Writer;
use App\Library\Csv\CsvHeaderNameManager;

//
$basePath = dirname(__DIR__);
require_once $basePath . '/core/bootstrap.php';
initialize();

//
perform();
exit;

// --------------------------------------------------------------------------------
//
// --------------------------------------------------------------------------------

/**
 *
 */
function perform()
{
    $previous_user = '';
    $current_user = '';

    list($outputHeaders, $rows) = getCsvContents();
    if (!$rows) {
        echo "沒有資料";
        exit;
    }

    $key1 = 'i';        // 第一組時間 的 欄位名稱
    $key2 = 'w';        // 第二組時間 的 欄位名稱
    $key3 = 'l';        // 第三組時間 的 欄位名稱
    $nameKey = 'umw';   // 以該欄位做為 user 分類名稱

    foreach ($rows as $index => $row) {

        $row['new_1'] = '';
        $row['new_2'] = '';
        $row['new_3'] = '';
        $current_user = $row[$nameKey];
        if ($previous_user === $current_user) {

            $previousIndex = $index - 1;

            $previousTime = $rows[$previousIndex]['new_1'];
            $row['new_1'] = calculateTimePlus($row[$key1], $previousTime);
            $rows[$previousIndex]['new_1'] = '';

            $previousTime = $rows[$previousIndex]['new_2'];
            $row['new_2'] = calculateTimePlus($row[$key2], $previousTime);
            $rows[$previousIndex]['new_2'] = '';

            $previousTime = $rows[$previousIndex]['new_3'];
            $row['new_3'] = calculateTimePlus($row[$key3], $previousTime);
            $rows[$previousIndex]['new_3'] = '';

        }
        else {
            $row['new_1'] = calculateTimePlus($row[$key1]);
            $row['new_2'] = calculateTimePlus($row[$key2]);
            $row['new_3'] = calculateTimePlus($row[$key3]);
        }

        $rows[$index] = $row;
        $previous_user = $current_user;
    }

    // pr($rows);
    downloadCsv($outputHeaders, $rows);

}

// --------------------------------------------------------------------------------
//
// --------------------------------------------------------------------------------

/**
 * 對 天數 及 時數 做計算
 * 時數過 8 則進位到 天數
 *
 * @param string $timeString     format hh:mm:dd
 * @param string $plusTimeString format hh:mm:dd
 * @return string
 */
function calculateTimePlus($timeString, $plusTimeString='')
{
    $parseTimeFunc = function($myTime) {
        preg_match_all("/([0-9]{2}):([0-9]{2}):([0-9]{2})/is", $myTime, $matches);
        return $matches;
    };

    $times = $parseTimeFunc($timeString);
    $day    = (int) $times[1][0];
    $hour   = (int) $times[2][0];
    $minute = (int) $times[3][0];

    // 如果要相加
    if ($plusTimeString) {

        $plusTimes  = $parseTimeFunc($plusTimeString);
        $plusDay    = (int) $plusTimes[1][0];
        $plusHoure  = (int) $plusTimes[2][0];
        $plusMinute = (int) $plusTimes[3][0];

        $day    += $plusDay;
        $hour   += $plusHoure;
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
function getCsvContents()
{
    $varPath = getProjectPath('/var');
    $fileRule = "{$varPath}/*.[cC][sS][vV]";
    $files = glob($fileRule, GLOB_ERR);
    $fileCount = count($files);
    if ($fileCount < 1) {
        echo "{$varPath} 目錄中找不到 csv 檔案";
        exit;
    }
    elseif ($fileCount > 1) {
        echo "{$varPath} 目錄有多個 csv 檔案, 只保留一個檔案";
        exit;
    }

    $csvFile = $files[0];
    return parseCsv($csvFile);
}

/**
 *
 */
function parseCsv($file)
{
    $csv = Reader::createFromPath($file);
    $originHeaders = $csv->fetchOne();
    $headers = CsvHeaderNameManager::convert($originHeaders);

    $index = 0;
    foreach ($csv->fetchAssoc($headers) as $row) {
        $index++;
        if (1 === $index) {
            // is header
            continue;
        }

        $rows[] = $row;
    }

    return [$originHeaders, $rows];
}

/**
 *
 */
function downloadCsv(array $headers, array $rows)
{
    $csv = Writer::createFromFileObject(new SplTempFileObject());
    $csv->insertOne($headers);

    foreach ($rows as $row) {
        $csv->insertOne($row);
    }

    $outputPath = getProjectPath('/var/output.csv');
    $outputName = 'output_' . date('Y-m-d') . '.csv';
    //$csv->setOutputBOM(Reader::BOM_UTF8);
    $csv->output($outputName);
}
