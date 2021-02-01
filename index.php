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
];

$goodsService = new GoodsService($sheetData, $columnNames);
$allGoods = $goodsService->getAll();

$ids = [];
foreach ($allGoods as $good)
{
//    if ($goodId = $good->getGoodId())
//        $ids[$good->vendorCode] =
//            [
//                'id' => $goodId,
//                'iblockId' => $iblockId = $good->getIblockId($goodId),
//                'tableName' => $good->checkIblockTableExist($iblockId),
//            ];
//
//        if ($ids[$good->vendorCode]['tableName'])
//        {
//            $goodPrice = $good->getGoodPrice($ids[$good->vendorCode]['tableName'], $ids[$good->vendorCode]['id']);
//            $ids[$good->vendorCode]['goodPrice'] = $goodPrice;
//        }
    print_r($good);

}

print_r($ids);