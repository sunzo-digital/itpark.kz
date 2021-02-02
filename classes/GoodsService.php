<?php


namespace Goods;


use DB;
use Generator;
use PDO;

class GoodsService
{
    private $sheetData;
    private $columnNames;
    private $columnKeys;
    private $pdo;

    public function __construct(array $sheetData, array $columnNames)
    {
        $this->sheetData = $sheetData;
        $this->columnNames = $columnNames;
        $this->columnKeys = self::getColumnKeys($this->columnNames, $this->sheetData);
        $this->pdo = DB::getInstance()->pdo;
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

    public function getAll(): array
    {
        $itemGenerator = self::itemGenerator();
        $res = [];

        foreach ($itemGenerator as $item)
        {
            $dbData = $this->getDBdata($item);
            if ($dbData['error']) continue;
            $res[] = new GoodModel(array_merge($item, $dbData));
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

    public function getDBdata(array $item): array
    {
     $res['goodId'] = $this->getGoodId($item['vendorCodeCell']);
        if (!$res['goodId'])
        {
            $errorMsg = "{$item['vendorCodeCell']}: артикул не найден в базе данных";
            error_log('[' . date('d.m.Y H:i:s') . "] {$errorMsg}" . PHP_EOL,3,'DB_errors.log');
            return ['error' => $errorMsg];
        }

        $res['iblockId'] = $this->getIblockId($res['goodId']);
        if (!$res['iblockId'])
        {
            $errorMsg = "{$item['vendorCodeCell']}: не найден id каталога (iblock id)";
            error_log('[' . date('d.m.Y H:i:s') . "] {$errorMsg}" . PHP_EOL, 3, 'DB_errors.log');
            return ['error' => $errorMsg];
        }

        $res['tableName'] = $this->getIblockTableName($res['iblockId']);
        if (!$res['tableName'])
        {
            $errorMsg = "{$item['vendorCodeCell']}: не найдена таблица каталога";
            error_log('[' . date('d.m.Y H:i:s') . "] {$errorMsg}" . PHP_EOL,3,'DB_errors.log');
            return ['error' => $errorMsg];
        }

        return $res;
    }


    //TODO протестировать эту функцию
    public function getGoodId($vendorCode)
    {
        $query = "SELECT `IBLOCK_ELEMENT_ID` FROM `b_iblock_element_property` WHERE CONVERT(`VALUE` USING utf8) LIKE ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['%' . $vendorCode . '%']);
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
    public function getIblockTableName(int $iblockId)
    {
        $sql = "SELECT IF(COUNT(*)>0, 1, 0) AS 'isExist' FROM `information_schema`.`TABLES`
        WHERE 1 AND `TABLE_SCHEMA`='itpark' AND `TABLE_NAME`= ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['b_iblock_' . $iblockId . '_index']);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['isExist'] ? 'b_iblock_' . $iblockId . '_index' : false;
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