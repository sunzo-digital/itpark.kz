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
    public $currentPrice;

    private $pdo;


    public function __construct($pars)
    {
        $this->pdo = DB::getInstance()->pdo;

        $this->number = $pars['numberCell'];
        $this->vendorCode = (string) $pars['vendorCodeCell'];
        $this->item = $pars['itemCell'];
        $this->unit = $pars['unitCell'];
        $this->newPrice = str_replace(',', '', $pars['priceCell']);
        $this->sectionId = str_replace(',', '', $pars['sectionId']);
        $this->id = $pars['goodId'];
        $this->iblockId = $pars['iblockId'];
        $this->tableName = $pars['tableName'];
        $this->currentPrice = $this->getCurrentPrice();
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

    // Нужны ли в этих методах подготовленные запросы? По сути все данные приходят из БД.
    public function updatePrice()
    {
        $sql = "UPDATE `{$this->tableName}` SET `VALUE_NUM` = {$this->newPrice} WHERE `ELEMENT_ID` = {$this->id} 
        AND `SECTION_ID` = {$this->sectionId} AND `VALUE` = 1";
        $this->pdo->query($sql);

       if( (int) $this->getCurrentPrice() !== (int) $this->newPrice )
       {
           $errorMsg = "{$this->vendorCode}: не найдено поле цены в таблице {$this->tableName}";
           error_log('[' . date('d.m.Y H:i:s') . "] {$errorMsg}" . PHP_EOL,3,'DB_errors.log');
           return false;
       }
       return true;
    }

    public function getCurrentPrice()
    {
        $sql = "SELECT `VALUE_NUM` FROM {$this->tableName} WHERE `ELEMENT_ID` = {$this->id} 
        AND `SECTION_ID` = {$this->sectionId} AND `VALUE` = 1";
        $stmt = $this->pdo->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->currentPrice = $res['VALUE_NUM'];
        return $res['VALUE_NUM'];
    }
}