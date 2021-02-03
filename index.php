<?php

use Goods\GoodsService;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once 'vendor/autoload.php';
require_once 'classes/DB.php';
require_once 'classes/GoodModel.php';
require_once 'classes/GoodsService.php';

echo '<pre>';

$inputFileName = __DIR__ . '/table.xlsx';
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
foreach ($allGoods as $good)
{
    $good->updatePrice();
}