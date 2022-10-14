<?php
/*
* Файл local/modules/afonya.nsc/lib/register.php
*/

namespace Afonya\NSC;

use Bitrix\Main\Config\Option;

class Register
{

    public function elementAdd(&$arFields)
    {
        self::saveEvent(
            array(
                'IBLOCK_ID' => $arFields['IBLOCK_ID'],
                'ID' => $arFields['ID'],
                'TYPE' => 'ADD'
            )
        );
    }

    public function elementUpdate(&$arFields)
    {
        self::saveEvent(
            array(
                'IBLOCK_ID' => $arFields['IBLOCK_ID'],
                'ID' => $arFields['ID'],
                'TYPE' => 'UPDATE'
            )
        );
    }

    public function elementDel($arElement)
    {
        self::saveEvent(
            array(
                'IBLOCK_ID' => $arElement['IBLOCK_ID'],
                'ID' => $arElement['ID'],
                'TYPE' => 'DELETE'
            )
        );
    }

    public function saveEvent($event)
    {
        // Если выбранный блок новостей:
        if (self::isNewsBlock($event["IBLOCK_ID"])) {

            global $USER;

            $result = EventsTable::add(
                array(
                    'USER_ID' => $USER->getId(),
                    'EVENT' => $event['TYPE'],
                    'ARTICLE_ID' => $event['ID'],
                    'TIME' => new \Bitrix\Main\Type\DateTime()
                )
            );
            if ($result->isSuccess()) {
                $id = $result->getId();
            }
        }
    }

    protected function isNewsBlock($iblockId) : bool
    {
        if (Option::get("afonya.nsc", 'news_block') == $iblockId)
            return  True;
        else
            return False;
    }
}
