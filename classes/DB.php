<?php


class DB
{
    public $pdo;
    private static $instances = [];

    protected function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=itpark', 'root', 'root');
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance(): DB
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

}