<?php

//$query = "SELECT * FROM `b_iblock_element_iprop` WHERE VALUE LIKE '%Шкаф настенный 19\" 6U 600X600X370, цвет серый, дверь стекло%'";
$pdo = new PDO('mysql:host=localhost;dbname=itpark', 'root', 'root');

// Так можно получить данные по шкафу, но ни артикула, ни цены нет
$query = "SELECT * FROM `b_iblock_element` WHERE `NAME` LIKE 'Блок электрических розеток, 8 розеток, 1U, длина кабеля 2 м'";

//$query = "SELECT * FROM `b_iblock_element` WHERE `ID` = 6885";

$res = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

echo '<pre>';
print_r($res);

$a = [
    'IBLOCK_ID' => 77, // id блока. "Каталог"
    'SECTION_ID' => 1201, // Категория
    'ELEMENT_ID' => 6885, // id товара
    'IPROP_ID' => 9804, // 9804, 9805, 9806, 9807
];


/*Таблица b_iblock_77_index содержит id категорий, id товаров категорий, ?, id валюты, цена, ?
Таблица b_iblock_element_property содержит артикул (VALUE)*/

/*
    1) Из таблицы b_iblock_element_property по артиклу (VALUE) достаем id товара (IBLOCK_ELEMENT_ID).
    2) Из таблицы b_iblock_element по ID получаем категорию (IBLOCK_ID) и название (Определись со столбцом)
    3) Из таблицы b_iblock_77_index по id (ELEMNT_ID) товара достаем его цену (VALUE)
 */