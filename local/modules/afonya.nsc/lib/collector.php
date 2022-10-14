<?php
/*
* Файл local/modules/afonya.nsc/collector.php
*/

namespace Afonya\NSC;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity;

class Collector
{
    public int $fromID;
    public int $toID;
    public string $fromTime;
    public string $toTime;
    public array $arCount;

    public function __construct()
    {
        // Определяем диапазоны выборки
        $this->setFrom();
        $this->setTo();

        // Получаем ID пользователя который сделал максимальное число правок
        $arParams = array('EVENTS' => 'UPDATE', 'TYPE' => 'USER_ID');

        $userID = $this->getCount($arParams);

        // Получаем ФИО пользователя
        $rsUser = \CUser::GetByID($userID);
        $arUser = $rsUser->Fetch();

        // Получаем статистику и ФИО
        $this->arCount = array(
            'UPDATED' => self::getCount(array('EVENTS' => 'UPDATE')),
            'ADDED' => $this->getCount(array('EVENTS' => 'ADD')),
            'DELETED' => $this->getCount(array('EVENTS' => 'DELETE')),
            'FIO' => $arUser['LAST_NAME'] . $arUser['NAME'] . $arUser['SECOND_NAME'],
        );
    }

    protected function setFrom()
    {

        // Получаем ID после которого идет выборка
        if (!$this->fromID = (int) Option::get("afonya.nsc", 'last_id')) {
            $this->fromID = 0;
        }

        // Время с которого будет показана выборка
        if (!$this->fromTime = Option::get("afonya.nsc", 'last_time')) {
            $this->fromTime = 'момента установки модуля';
        }
    }

    protected function setTo()
    {

        // Берем последний ID с которым будем работать
        $result = EventsTable::getList(
            array(
                'select' => array('ID', 'TIME'),
                'order' => array('TIME' => 'desc'),
                'limit' => 1
            )
        )->fetch();

        // Сохраняем ID и время
        $this->toID = (int) $result['ID'];
        $this->toTime = $result['TIME'] ? $result['TIME'] : 'нет данных';
    }

    protected function getCount($arParams)
    {
        if (!$arParams['TYPE'] == 'USER_ID') {
            $arParams['TYPE'] = 'ARTICLE_ID';
        }

        $result =
            EventsTable::getList(
                array(
                    'select' => array('CNT', $arParams['TYPE']),
                    'filter' => array(
                        '>ID' => $this->fromID,
                        '<=ID' => $this->toID,
                        '=EVENT' => $arParams['EVENTS'],
                    ),
                    'order' => array('CNT' => 'desc'),
                    'runtime' => array(
                        new Entity\ExpressionField('CNT', 'COUNT(*)')
                    )
                )
            )->fetchAll();

        // Отправляем либо строку с ID пользователя
        if ($arParams['TYPE'] == 'USER_ID')
            return $result[0];
        // Либо итоговое число записей
        else
            return count($result);
    }
}
