<?php


namespace Goods;


use DB;
use PDO;

class GoodModel
{
    // table data
    public $number;
    public $vendorCode;
    public $item;
    public $unit;
    public $newPrice;
    public $sectionId; // Сейчас в таблице такого столбца нет, нужно чтобы клиент добавил

    // db data
    public $id;
    public $iblockId;
    public $goodPrice;
    public $tableName;

    private $pdo;


    public function __construct($pars)
    {
        $this->number = $pars['numberCell'];
        $this->vendorCode = (string) $pars['vendorCodeCell'];
        $this->item = $pars['itemCell'];
        $this->unit = $pars['unitCell'];
        $this->newPrice = str_replace(',', '', $pars['priceCell']);
        $this->sectionId = str_replace(',', '', $pars['sectionId']);

        $this->pdo = DB::getInstance()->pdo;

        try
        {
            if ($id = $this->getGoodId())
            {
                $this->id = $id;
                $this->iblockId = $iblockId = $this->getIblockId($id);
                $this->tableName = $tableName = $this->checkIblockTableExist($iblockId);
                if ($tableName) {
                    $this->goodPrice = $this->getGoodPrice($tableName, $id);
                }
            }
        }
        catch (\Error $e)
        {
            error_log($e, 3, "construct.log");
        }

    }

    public function toArray(): array
    {
        return
            [
                'number' => $this->number,
                'vendorCode' => $this->vendorCode,
                'item' => $this->item,
                'unit' => $this->unit,
                'newPrice' => $this->newPrice,
                'id' => $this->id,
                'iblockId' => $this->iblockId,
                'goodPrice' => $this->goodPrice,
                'tableName' => $this->tableName,
            ];
    }


    public function getGoodId()
    {
        $query = "SELECT `IBLOCK_ELEMENT_ID` FROM `b_iblock_element_property` WHERE CONVERT(`VALUE` USING utf8) LIKE ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['%' . $this->vendorCode . '%']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['IBLOCK_ELEMENT_ID'];
    }

    public function getIblockId($goodId): int
    {
        $sql = "SELECT `IBLOCK_ID` FROM `b_iblock_element` WHERE `ID` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$goodId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['IBLOCK_ID'];
    }

    // Странный поиск, было бы лучше, если бы в таблику b_iblock добавили поле с именами таблиц
    // Эта проблема также решается, если у нас есть фиксированный каталог (например b_iblock_77_index)
    public function checkIblockTableExist(int $iblockId)
    {
        $sql = "SELECT IF(COUNT(*)>0, 1, 0) AS 'isExist' FROM `information_schema`.`TABLES`
        WHERE 1 AND `TABLE_SCHEMA`='itpark' AND `TABLE_NAME`= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['b_iblock_' . $iblockId . '_index']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['isExist'] ? 'b_iblock_' . $iblockId . '_index' : false;
    }

    // Условие поиска нужно изменить. Для этого необходимо понимать значения столбцов в b_iblock_77_index
    public function getGoodPrice(string $table, int $goodId)
    {
        $sql = "SELECT `VALUE_NUM` FROM `$table` WHERE `ELEMENT_ID` = ? AND `VALUE_NUM` > 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$goodId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['VALUE_NUM'];
    }

    public function setGoodPrice()
    {
//        $sql = "SELECT `VALUE_NUM` FROM `$this->tableName` WHERE `ELEMENT_ID` = ? AND `VALUE_NUM` > 0";
//        $stmt = $this->pdo->prepare($sql);
//        $stmt->execute([$goodId]);
//        $res = $stmt->fetch(PDO::FETCH_ASSOC);

    }

}