<?php


namespace Goods;


use DB;
use Generator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;

class GoodsService
{
    private $sheetData;
    private $columnNames = [
        'numberCell' => '№',
        'vendorCodeCell' => 'Артикул',
        'itemCell' => 'Наименование',
        'unitCell' => 'Ед.изм.',
        'priceCell' => 'Цена, тенге с НДС',
    ];
    private $columnKeys;

    private $pdo;
    private $errorLogger;
    private $successLogger;
    private $errors = [];

    public function __construct(array $sheetData)
    {
        $this->pdo = DB::getInstance()->pdo;

        $this->errorLogger = new Logger('errorLogger');
        $this->errorLogger->pushHandler(new StreamHandler('logs/'.date('d-m-Y').'.error.log'), Logger::WARNING);
        $this->successLogger = new Logger('successLogger');
        $this->successLogger->pushHandler(new StreamHandler('logs/'.date('d-m-Y').'.success.log'), Logger::INFO);

        $this->sheetData = $sheetData;
        $this->columnKeys = self::getColumnKeys($this->columnNames, $this->sheetData);
    }

    protected function getColumnKeys(array $columnNames, array $sheetData): array
    {
        $res = [];

        foreach ($columnNames as $k => $v) {
            foreach ($sheetData as $row) {
                if (array_search($v, $row)) {
                    $res[$k] = array_search($v, $row);
                    break;
                }
            }
        }

        return $res;
    }

    public function getOneByVendorCode(string $vendorCode)
    {
        $res = [];

        foreach ($this->sheetData as $row)
        {
            if (in_array($vendorCode, $row))
            {
                foreach ($this->columnKeys as $k => $v)
                {
                    $res[$k] = $row[$v];
                }
            }
        }

        $res['goodId'] = $this->getGoodId($vendorCode);
        if (!$res['goodId']) return false;

        return new GoodModel($res);
    }

    public function getAll(): array
    {
        $itemGenerator = self::itemGenerator();
        $res = [];

        foreach ($itemGenerator as $item)
        {
            $item['goodId'] = $this->getGoodId( $item['vendorCodeCell'] );
            if (!$item['goodId']) continue;

            $res[] = new GoodModel($item);
        }

        return $res;
    }

    protected function itemGenerator(): Generator
    {
        foreach ($this->sheetData as $row) {
            $item = [];

            if ($row[$this->columnKeys['vendorCodeCell']]
                && $row[$this->columnKeys['vendorCodeCell']] != $this->columnNames['vendorCodeCell']) {
                foreach ($this->columnKeys as $k => $v) {
                    $item[$k] = $row[$v];
                }

                yield $item;
            }
        }
    }

    protected function getGoodId($vendorCode)
    {
        $query = "SELECT `IBLOCK_ELEMENT_ID` FROM `b_iblock_element_property` WHERE CONVERT(`VALUE` USING utf8) LIKE ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['%' . $vendorCode . '%']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res['IBLOCK_ELEMENT_ID'])
        {
            $errorMsg = "{$vendorCode}: артикул не найден в базе данных";
            $this->errorLogger->warning($errorMsg);
            $this->errors[] = $errorMsg;
            return false;
        }

        $this->successLogger->info("{$vendorCode}: данные из БД успешно получены");
        return $res['IBLOCK_ELEMENT_ID'];
    }

    public function getErrors()
    {
        $res = $this->errors;
        $this->errors = [];
        return $res;
    }

}