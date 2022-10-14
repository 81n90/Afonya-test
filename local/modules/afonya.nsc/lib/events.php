<?php
/*
* Файл local/modules/afonya.nsc/lib/events.php
*/

namespace Afonya\NSC;

use Bitrix\Main\Entity;


class EventsTable extends Entity\DataManager
{
    // Название таблицы для хранения сущности
    public static function getTableName()
    {
        return 'b_afonya_nsc_events';
    }

    // Описание сущности
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                    'primary' => true,
                    'autocomplete' => true
                )
            ),
            new Entity\StringField('USER_ID', array(
                    'required' => true,
                )
            ),
            new Entity\StringField('ARTICLE_ID', array(
                    'required' => true,
                )
            ),
            new Entity\EnumField('EVENT', array(
                    'values' => array('ADD', 'UPDATE', 'DELETE'),
                    'required' => true,
                )
            ),
            new Entity\DatetimeField('TIME', array(
                    'required' => true,
                )
            ),
            new Entity\ExpressionField('PERIOD',
                'TIMEDIFF(NOW(), %s)', array('TIME')
            )
        );
    }
}
