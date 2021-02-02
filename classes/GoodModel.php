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
        $this->id = $pars['goodId'];
        $this->iblockId = $pars['iblockId'];
        $this->tableName = $pars['tableName'];

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
                'newPrice' => $this->newPrice,
                'sectionId' => $this->sectionId,
                'id' => $this->id,
                'iblockId' => $this->iblockId,
                'tableName' => $this->tableName,
            ];
    }

    public function updatePrice()
    {
        $sql = "UPDATE `{$this->tableName}` SET `VALUE_NUM` = {$this->newPrice} WHERE `ELEMENT_ID` = {$this->id} 
        AND `SECTION_ID` = {$this->sectionId} AND `VALUE` = 1";
        return $this->pdo->query($sql);
    }

    // Условие поиска нужно изменить. Для этого необходимо понимать значения столбцов в b_iblock_77_index
//    public function getGoodPrice(string $table, int $goodId)
//    {
//        $sql = "SELECT `VALUE_NUM` FROM `$table` WHERE `ELEMENT_ID` = ? AND `VALUE_NUM` > 0";
//        $stmt = $this->pdo->prepare($sql);
//        $stmt->execute([$goodId]);
//        $res = $stmt->fetch(PDO::FETCH_ASSOC);
//        return $res['VALUE_NUM'];
//    }
}