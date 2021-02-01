<?php


namespace Goods;


use Generator;

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

    protected function getColumnKeys(array $columnNames, array $sheetData) : array
    {
        $res = [];

        foreach ($columnNames as $k => $v)
        {
            foreach ($sheetData as $row)
            {
                if (array_search($v, $row))
                {
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

    public function getAll(): array
    {
        $itemGenerator = self::itemGenerator();
        $res = [];

        foreach ($itemGenerator as $item)
            $res[] = new GoodModel($item);

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