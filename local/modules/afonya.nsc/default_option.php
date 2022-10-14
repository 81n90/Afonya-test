<?php
/*
 * Файл local/modules/afonya.nsc/default_option.php
 */

use Bitrix\Main\Loader;

// подключаем модуль для получения инфоблоков
Loader::includeModule("iblock");

// Получение инфоблоков новостей
$dbIBlockNews = CIBlock::GetList(
    array("sort" => "asc",),
    array("ACTIVE" => "Y", "TYPE" => 'news')
);

// Берем первый найденный инфоблок с новостями для установки по умолчанию
if ($arIBlockNews = $dbIBlockNews->Fetch()) {
    $afonya_nsc_default_option = array(
        "news_block" => $arIBlockNews["ID"],
        "email" => "81n90@mail.ru"
    );
}
