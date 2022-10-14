<?php
/*
* Файл local/modules/afonya.nsc/sender.php
*/

namespace Afonya\NSC;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity;

class Sender
{
    public function send()
    {
        $sendParams = new Collector();

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

        if (\CEvent::Send("AFONYA_NSC_STAT", 's1', $arEventFields)) {
            // Если отправилось - сохраняем пределы диапазона
            Option::set("afonya.nsc", 'last_id', $sendParams->toID);
            Option::set("afonya.nsc", 'last_time', $sendParams->toTime);
        }

        // Возврат функции класса, для повторной периодической отработки агента
        return static::class."::send();";
    }
}
