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
        'sectionId' => 'section_id',
    ];
    private $columnKeys;

    private $pdo;
    private $errorLogger;
    private $successLogger;

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

        $dbData = $this->getDBdata($res);
        if (!$dbData) return null;
        return new GoodModel(array_merge($res, $dbData));
    }

    public function getAll(): array
    {
        $itemGenerator = self::itemGenerator();
        $res = [];

        foreach ($itemGenerator as $item)
        {
            $dbData = $this->getDBdata($item);
            if (!$dbData) continue;
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

    public function getDBdata(array $item)
    {

     $res['goodId'] = $this->getGoodId($item['vendorCodeCell']);
        if (!$res['goodId'])
        {
            $this->errorLogger->warning("{$item['vendorCodeCell']}: не найден артикул в базе данных");
            return false;
        }

        $res['iblockId'] = $this->getIblockId($res['goodId']);
        if (!$res['iblockId'])
        {
            $this->errorLogger->warning("{$item['vendorCodeCell']}: не найден id каталога (IBLOCK_ID)");
            return false;
        }

        $res['sectionId'] = $this->getIblockSectionId($res['goodId']);
        if (!$res['sectionId'])
        {
            $this->errorLogger->warning("{$item['vendorCodeCell']}: не найден id категории (IBLOCK_SECTION_ID)");
            return false;
        }

        $this->successLogger->info("{$item['vendorCodeCell']}: данные из БД успешно получены");
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

    public function getIblockSectionId($goodId): int
    {
        $sql = "SELECT `IBLOCK_SECTION_ID` FROM `b_iblock_element` WHERE `ID` = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$goodId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['IBLOCK_SECTION_ID'];
    }

}