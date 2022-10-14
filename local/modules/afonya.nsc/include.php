<?php
/*
* Файл local/modules/afonya.nsc/include.php
*/

// Регистрируем классы модуля
Bitrix\Main\Loader::registerAutoloadClasses(
    'afonya.nsc',
    array(
        'Afonya\NSC\Register' => 'lib/register.php',
        'Afonya\NSC\EventsTable' => 'lib/events.php',
        'Afonya\NSC\Sender' => 'lib/sender.php',
    )
);
