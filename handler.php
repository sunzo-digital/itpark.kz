<?php
session_start();

$uploadRes = uploadFile();
if ($uploadRes['error'])
{
    $_SESSION['message'] = $uploadRes['error'];
    header("Location: index.php?password=12345");
}

use Goods\GoodsService;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once 'vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/GoodModel.php';
require_once 'classes/GoodsService.php';

$inputFileName = $uploadRes['dir'];
$spreadsheet = IOFactory::load($inputFileName);
$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

$columnNames = [
    'numberCell' => '№',
    'vendorCodeCell' => 'Артикул',
    'itemCell' => 'Наименование',
    'unitCell' => 'Ед.изм.',
    'priceCell' => 'Цена, тенге с НДС',
    'sectionId' => 'section_id',
];

$goodsService = new GoodsService($sheetData, $columnNames);
$allGoods = $goodsService->getAll();
foreach ($allGoods as $good) $good->updatePrice();

$_SESSION['message'] = 'Цены обновленны. Результаты смотрите в логах: '.
    date('d-m-Y').'.error.log и ' . date('d-m-Y').'.success.log';
header("Location: index.php?password=12345");

//$one = $goodsService->getOneByVendorCode('00000011712')->toArray();

function uploadFile():array
{
    if (isset($_POST['submit']) && $_POST['submit'] == 'upload')
    {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK)
        {
            // get details of the uploaded file
            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];
            $fileSize = $_FILES['file']['size'];
            $fileType = $_FILES['file']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // sanitize file-name
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

            // check if file has one of the following extensions
            $allowedExtensions = array('xls', 'xlsx',);

            if (in_array($fileExtension, $allowedExtensions))
            {
                // directory in which the uploaded file will be moved
                $uploadFileDir = __DIR__.'/uploaded_files/';
                $dest_path = $uploadFileDir.$newFileName;

                if(move_uploaded_file($fileTmpPath, $dest_path))
                {
                    return ['dir' => $dest_path];
                }
                else
                {
                    return ['error' => 'There was some error moving the file to upload directory. 
                    Please make sure the upload directory is writable by web server.'];
                }
            }
            else
            {
                return ['error' => 'Upload failed. Allowed file types: ' . implode(', ', $allowedExtensions)];
            }
        }
        else
        {
            return ['error' => $_FILES['file']['error']];
        }
    }
    return ['error' => 'Invalid POST request'];
}


//$fd = fopen("after.json", 'w') or die("не удалось создать файл");
//$res = [];
//foreach ($allGoods as $good)
//{
//    $good->updatePrice();
//    $res[$good->vendorCode] = ['currentPrice' => $good->currentPrice, 'newPrice' => $good->newPrice];
//}
//
//fputs($fd,json_encode($res));
//fclose($fd);
