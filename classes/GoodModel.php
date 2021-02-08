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
    public $tableName;
    public $currentPrice;

    private $pdo;
    private $errorLogger;
    private $successLogger;


    public function __construct($pars)
    {
        $this->pdo = DB::getInstance()->pdo;

        $this->errorLogger = new Logger('errorLogger');
        $this->errorLogger->pushHandler(new StreamHandler('logs/' . date('d-m-Y') . '.error.log'), Logger::WARNING);
        $this->successLogger = new Logger('successLogger');
        $this->successLogger->pushHandler(new StreamHandler('logs/' . date('d-m-Y') . '.success.log'), Logger::INFO);

        $this->number = $pars['numberCell'];
        $this->vendorCode = (string)$pars['vendorCodeCell'];
        $this->item = $pars['itemCell'];
        $this->unit = $pars['unitCell'];
        $this->newPrice = str_replace(',', '', $pars['priceCell']);
        $this->tableName = 'b_catalog_price';

        $this->id = $pars['goodId'];
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
                'tableName' => $this->tableName,
                'item' => $this->item,
                'unit' => $this->unit,
                'number' => $this->number,
            ];
    }

    public function updatePrice(): bool
    {
        $sql = "UPDATE `{$this->tableName}` SET `PRICE` = {$this->newPrice} WHERE `PRODUCT_ID` = {$this->id};
        UPDATE `{$this->tableName}` SET `PRICE_SCALE` = {$this->newPrice} WHERE `PRODUCT_ID` = {$this->id}";
        $this->pdo->query($sql);

        if ((int)$this->getCurrentPrice() != (int)$this->newPrice) {
            $this->errorLogger->warning("{$this->vendorCode}: не удалось обновить цену");
            return false;
        }

        $this->successLogger->info("{Цена успешно обновлена:", [self::toArray()]);
        return true;
    }

    public function getCurrentPrice()
    {
        $sql = "SELECT `PRICE` FROM {$this->tableName} WHERE `PRODUCT_ID` = {$this->id}";
        $stmt = $this->pdo->query($sql);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($res['PRICE']) {
            $this->currentPrice = $res['PRICE'];
            return $res['PRICE'];
        }

        $this->errorLogger->warning("{$this->vendorCode}: не найдено поле цены в таблице {$this->tableName}");
        return false;
    }
}