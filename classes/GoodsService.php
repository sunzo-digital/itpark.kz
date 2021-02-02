<?php


namespace Goods;


use Generator;
use PDO;

class GoodsService
{
    private $sheetData;
    private $columnNames;
    private $columnKeys;

    public function __construct(array $sheetData, array $columnNames)
    {
        $this->sheetData = $sheetData;
        $this->columnNames = $columnNames;
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

    // TODO допиши обработку ошибок
    public function getOneByVendorCode(string $vendorCode): GoodModel
    {
        $res = [];

        foreach ($this->sheetData as $row) {
            if (in_array($vendorCode, $row)) {
                foreach ($this->columnKeys as $k => $v) {
                    $res[$k] = $row[$v];
                }
            }
        }

        return new GoodModel($res);
    }

    // TODO нужно получать все необходимые данные из БД здесь и проверять их. После этого создавать объект или логировать ошибку
    public function getAll(): array
    {
        $itemGenerator = self::itemGenerator();
        $res = [];

        foreach ($itemGenerator as $item)
            if ($this->checkElementProperty($item['vendorCodeCell']))
                $res[] = new GoodModel($item);
            else error_log('[' . date('d.m.Y H:i:s'). "] {$item['vendorCodeCell']}: артикул не найден в базе данных".PHP_EOL,
                3, 'vendor_codes.log');

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

    // TODO как сделать подготовленный запрос
    public function checkElementProperty(string $vendorCode): bool
    {
        $sql = "SELECT * FROM `b_iblock_element_property` WHERE `VALUE` = '{$vendorCode}'";
        $stmt = \DB::getInstance()->pdo->query($sql);
        if (!$stmt) return false;
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($res);
    }

    /*
    private static $instances = [];

    protected function __construct()
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(): GoodsService
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }*/

}