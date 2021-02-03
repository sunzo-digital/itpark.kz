<?php


namespace Goods;


use DB;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;

class GoodModel
{
    // table data
    public $number;
    public $vendorCode;
    public $item;
    public $unit;
    public $newPrice;
    // db data

    public $id;
    public $iblockId;
    public $sectionId;
    public $tableName;
    public $currentPrice;

    private $pdo;
    private $errorLogger;
    private $successLogger;


    public function __construct($pars)
    {
        $this->pdo = DB::getInstance()->pdo;

        $this->errorLogger = new Logger('errorLogger');
        $this->errorLogger->pushHandler(new StreamHandler('logs/'.date('d-m-Y').'.error.log'), Logger::WARNING);
        $this->successLogger = new Logger('successLogger');
        $this->successLogger->pushHandler(new StreamHandler('logs/'.date('d-m-Y').'.success.log'), Logger::INFO);

        $this->number = $pars['numberCell'];
        $this->vendorCode = (string)$pars['vendorCodeCell'];
        $this->item = $pars['itemCell'];
        $this->unit = $pars['unitCell'];
        $this->newPrice = str_replace(',', '', $pars['priceCell']);

        $this->sectionId = str_replace(',', '', $pars['sectionId']);
        $this->id = $pars['goodId'];
        $this->iblockId = $pars['iblockId'];
        $this->sectionId = $pars['sectionId'];
        $this->tableName = $pars['tableName'];
        $this->currentPrice = $this->getCurrentPrice();
    }

    public function toArray(): array
    {
        return
            [
                'vendorCode' => $this->vendorCode,
                'id' => $this->id,
                'currentPrice' => $this->currentPrice,
                'newPrice' => $this->newPrice,
                'sectionId' => $this->sectionId,
                'iblockId' => $this->iblockId,
                'tableName' => $this->tableName,
                'item' => $this->item,
                'unit' => $this->unit,
                'number' => $this->number,
            ];
    }

    // Нужны ли в этих методах подготовленные запросы? По сути все данные приходят из БД.
    public function updatePrice(): bool
    {
        $sql = "UPDATE `{$this->tableName}` SET `VALUE_NUM` = {$this->newPrice} WHERE `ELEMENT_ID` = {$this->id} 
        AND `SECTION_ID` = {$this->sectionId} AND `VALUE` = 1";
        $this->pdo->query($sql);

        if ((int)$this->getCurrentPrice() !== (int)$this->newPrice)
        {
            $this->errorLogger->warning("{$this->vendorCode}: не удалось обновить цену");
            return false;
        }

        $this->successLogger->info("{Цена успешно обновлена:", [self::toArray()]);
        return true;
    }

    public function getCurrentPrice()
    {
        $sql = "SELECT `VALUE_NUM` FROM {$this->tableName} WHERE `ELEMENT_ID` = {$this->id} 
        AND `SECTION_ID` = {$this->sectionId} AND `VALUE` = 1";
        $stmt = $this->pdo->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res['VALUE_NUM'])
        {
            $this->currentPrice = $res['VALUE_NUM'];
            return $res['VALUE_NUM'];
        }

        $this->errorLogger->warning("{$this->vendorCode}: не найдено поле цены в таблице {$this->tableName}");
        return false;
    }
}