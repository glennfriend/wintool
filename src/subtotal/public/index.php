<?php
#!/usr/bin/php -q

$basePath = dirname(__DIR__);
require_once $basePath . '/core/bootstrap.php';
initialize($basePath);




echo 'hi, bye';
// perform();
exit;

// --------------------------------------------------------------------------------
//
// --------------------------------------------------------------------------------

/**
 *
 */
function perform()
{
    if ( phpversion() < '5.5' ) {
        /**
         *  @see array_column(), PHP 5.5
         */
        show("PHP Version need >= 5.5", true);
        exit;
    }

    if (!getParam('exec')) {
        show('---- debug mode ---- (參數 "exec" 執行寫入指令)');
    }

    Log::record('start PHP '. phpversion() );
    upgradeGoogleSheet();

    // create CSV file
    $csvFileName = getCsvFileName();
    makeCsvFile($csvFileName);

    // upload CSV file
    if (getParam('exec')) {
        uploadCsvFile($csvFileName);
    }

    show("done", true);
}

/**
 *
 */
function upgradeGoogleSheet()
{
    $token = GoogleApiHelper::getToken();
    if (!$token) {
        show('token error!', true);
        exit;
    }

    // long time ...
    $worksheet = GoogleApiHelper::getWorksheet(
        conf('google.spreadsheets.book'),
        conf('google.spreadsheets.sheet'),
        $token
    );
    if (!$worksheet) {
        show('campaigns sheet not found!', true);
        exit;
    }

    $sheet = new GoogleWorksheetManager($worksheet);
    $header = $sheet->getHeader();
    $count  = $sheet->getCount();

    for ( $i=0; $i<$count; $i++ ) {

        $row = $sheet->getRow($i);
        $row = filterUnusedCode($row);

        //
        $row = updateDate($row);
        $row = updateByFacebook($row, $header);

        //
        // show($row);


        // update sheet row
        if (getParam('exec')) {
            // 如果內容完全相同, 就不需要更新
            // 為了達到該效果, int 需要轉化為 string
            $originRow = $sheet->getRow($i);

            if ( md5(serialize($originRow)) === md5(serialize($row)) ) {
                echo "({$i}-same) ";
            }
            else {
                $result = writeSheet($row, $i, $sheet);
                if ($result) {
                    echo "{$i} ";
                }
                else {
                    echo "({$i}-udpate-fail) ";
                }
            }
        }

        // show message
        if (!isCli()) {
            ob_flush(); flush();
        }
    }

    show('');
}

/**
 *  資料寫入 google sheet
 *  @return true=有寫入, false=無寫入
 */
function writeSheet($row, $index, $sheet)
{
    try {
        $sheet->setRow($index, $row);
    }
    catch ( Exception $e) {
        show($e->getMessage(), true);
        exit;
    }

    return true;
}



/**
 *
 */
function updateDate( $row )
{
    $row['date'] = date("n/j/Y", time());
    $row['date'] = date('n/j/Y', strtotime($row['date'] . ' - 1 day'));
    return $row;
}

/**
 *
 */
function updateByFacebook($row, $header)
{
    $row['impressions'] = 0;
    $row['clicks']      = 0;
    $row['cost']        = 0;

    $items = getFacebookItems();
    foreach ($items as $item) {
        if ($row['campaign'] == $item['campaign_name']) {
            $row['cost']        = (string) (double) $item['spend'];
            $row['impressions'] = (string) (double) $item['impressions'];
            $row['clicks']      = (string) (double) $item['action_comment'];
            break;
        }
    }

    return $row;
}

/**
 *  cache facebook data
 */
function getFacebookItems()
{
    static $result;
    if ($result) {
        return $result;
    }

    $result = FacebookHelper::getWrapCampaignLevel();
    return $result;
}

/**
 *
 */
function getCsvFileName()
{
    $dateFormat = date('Y-m-d', time());
    $dateFormat = date('Y-m-d', strtotime($dateFormat . ' - 1 day'));
    $path = conf('public.base.path') . '/var/kenshoo_upload';
    $file = "WeddingDresses-UC_File-{$dateFormat}.csv";
    return $path . '/' . $file;
}
