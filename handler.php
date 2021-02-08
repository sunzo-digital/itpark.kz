<?php
session_start();

$uploadRes = uploadFile();
if ($uploadRes['error']) die($uploadRes['error']);


use Goods\GoodsService;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once 'vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/GoodModel.php';
require_once 'classes/GoodsService.php';

$inputFileName = $uploadRes['dir'];
$spreadsheet = IOFactory::load($inputFileName);
$sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

$goodsService = new GoodsService($sheetData);
$res = priceUpdate($goodsService);
$_SESSION['message'] = getResultHtml($res);
header("Location: index.php?password=12345");


function uploadFile(): array
{
    if (isset($_POST['submit']) && $_POST['submit'] == 'upload') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
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

            if (in_array($fileExtension, $allowedExtensions)) {
                // directory in which the uploaded file will be moved
                $uploadFileDir = __DIR__ . '/uploaded_files/';
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    return ['dir' => $dest_path];
                } else {
                    return ['error' => 'There was some error moving the file to upload directory. 
                    Please make sure the upload directory is writable by web server.'];
                }
            } else {
                return ['error' => 'Upload failed. Allowed file types: ' . implode(', ', $allowedExtensions)];
            }
        } else {
            return ['error' => $_FILES['file']['error']];
        }
    }
    return ['error' => 'Invalid POST request'];
}

function priceUpdate(GoodsService $goodsService): array
{
    $allGoods = $goodsService->getAll();
    $goodServiceErrors = $goodsService->getErrors();

    $successCount = 0;
    $errorCount = count($goodServiceErrors);
    $errorArr = [];

    foreach ($allGoods as $good) {
        if (!$good->updatePrice()) {
            $errorCount++;
            $errorArr[] = "{$good->vendorCode}: не удалось обновить цену";
            continue;
        }

        $successCount++;
    }

    return [
        'successCount' => $successCount,
        'errorCount' => $errorCount,
        'errorArr' => array_merge($errorArr, $goodServiceErrors)];
}

function getResultHtml(array $res) :string
{
    $html = '';
    $html .= "<p>Цен успешно обновлено: <b>{$res['successCount']}</b>.</p>";
    $html .= "<p>Не удалось обновить: <b>{$res['errorCount']}.</p>";
    $html .= '<br><table border="1"><caption>Ошибки</caption>';
    for ($i = 0, $j = count($res['errorArr']); $i !== $j; $i++)
    {
        $html .= '
        <tr>
            <th>№</th>
            <th>Ошибка</th>
        </tr>
        <tr>
            <td>'. ($i + 1) .'</td>
            <td>'. $res['errorArr'][$i] .'</td>
        </tr>';
    }
    $html .= '</table><br>';
    $html .= '<p> Подробная информация в логах: <b>' . date('d-m-Y') . '.error.log</b> и <b>' . date('d-m-Y') . '.success.log</b></p><hr>';

    return $html;
}