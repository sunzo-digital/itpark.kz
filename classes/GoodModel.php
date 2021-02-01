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
    public $price;

    private $pdo;


    public function __construct($pars)
    {
        $this->number = $pars['numberCell'];
        $this->vendorCode = $pars['vendorCodeCell'];
        $this->item = $pars['itemCell'];
        $this->unit = $pars['unitCell'];
        $this->price = $pars['priceCell'];

        $this->pdo = DB::getInstance()->pdo;
    }

    public function toArray(): array
    {
        return
            [
                'number' => $this->number,
                'vendorCode' => $this->vendorCode,
                'item' => $this->item,
                'unit' => $this->unit,
                'price' => $this->price,
            ];
    }

    public function checkElementProperty():bool
    {
        $query = "SELECT * FROM `b_iblock_element_property` WHERE `VALUE` = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$this->vendorCode]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return !empty($res);
    }


    public function getGoodId()
    {
        $query = "SELECT `IBLOCK_ELEMENT_ID` FROM `b_iblock_element_property` WHERE CONVERT(`VALUE` USING utf8) LIKE ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['%'.$this->vendorCode.'%']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['IBLOCK_ELEMENT_ID'];
    }

    public function getIblockId($goodId):int
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
        $stmt->execute(['b_iblock_'.$iblockId.'_index']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['isExist'] ? 'b_iblock_'.$iblockId.'_index' : false;
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

    public function setGoodPrice(int $price, int $goodId, string $table)
    {

    }

}