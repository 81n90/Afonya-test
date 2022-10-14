<?php
/*
* Файл local/modules/afonya.nsc/sender.php
*/

namespace Afonya\NSC;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity;

class Sender
{
    protected int $fromID;
    protected int $toID;
    protected string $fromTime;
    protected string $toTime;
    protected array $arCount;

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
            'UPDATED' => $this->getCount(array('EVENTS' => 'UPDATE')),
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
        if (!$this->fromID = (int) Option::get("afonya.nsc", 'last_time')) {
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
        )->fetchAll();

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

    public function send()
    {
        $sendParams = new Sender();

        // Формируем письмо
        $description = 'Статистика по новостям: ';
        $message = 'Статистика по новостям за период с ' . $sendParams->fromTime . ' по ' . $sendParams->toTime . ' <br/>';
        $message .= 'всего было добавлено: ' . $sendParams->arCount['ADDED'] . ' <br/>';
        $message .= 'всего было отредактировано: ' . $sendParams->arCount['UPDATED'] . ' <br/>';
        $message .= 'всего было удалено: ' . $sendParams->arCount['DELETED'] . ' <br/>';
        $message .= 'уникальных статей.<br/>';
        $message .= 'Наибольшее число изменений внес: ' . $sendParams->arCount['FIO'] . '<br/>';
        $arEventFields = array(
            "MESSAGE" => $message,
            "EMAIL_TO" => implode(",", Option::get("afonya.nsc", 'email')),
            "DESCRIPTION" => $description,
        );

        AddMessage2Log(
            $message,
            "afonya.nsc"
        );
        if (\CEvent::Send("AFONYA_NSC_STAT", 's1', $arEventFields)) {
            // Если отправилось - сохраняем пределы диапазона
            Option::get("afonya.nsc", 'last_id', $sendParams->toID);
            Option::get("afonya.nsc", 'last_time', $sendParams->toTime);
        }
    }
}
