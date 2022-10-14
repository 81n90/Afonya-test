<?php
/*
 * Файл local/modules/afonya.nsc/install/index.php
 */

use Afonya\NSC\EventsTable;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use \Bitrix\Main\Loader;

class afonya_nsc extends CModule
{

    public function __construct()
    {

        if (is_file(__DIR__ . '/version.php')) {

            $arModuleVersion = array();

            include_once(__DIR__ . '/version.php');
            $this->MODULE_ID = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME = 'News statistic collector';
            $this->MODULE_DESCRIPTION = 'Afonya news statistic collector';
            $this->PARTNER_NAME = 'Afonya';
        } else {
            CAdminMessage::showMessage(
                'File not found version.php'
            );
        }
    }

    public function doInstall()
    {

        global $APPLICATION;

        // мы используем функционал нового ядра D7 — поддерживает ли его система?
        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            // копируем файлы, необходимые для работы модуля
            $this->installFiles();
            // регистрируем модуль в системе
            ModuleManager::registerModule($this->MODULE_ID);
            // создаем таблицы БД, необходимые для работы модуля
            $this->installDB();
            // регистрируем обработчики событий
            $this->installEvents();
        } else {
            CAdminMessage::showMessage(
                'install error'
            );
            return;
        }

        $APPLICATION->includeAdminFile(
            'Install «Afonya News statistic collector»',
            __DIR__ . '/step.php'
        );
    }

    public function installFiles()
    {

    }

    public function installDB()
    {
        // Создаем таблицу в базе
        if (Loader::includeModule($this->MODULE_ID)) {
//            EventsTable::getEntity()->createDbTable();
        }

    }

    public function installEvents()
    {

        // Регистрируем событие - добавление элемента
        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementAdd',
            $this->MODULE_ID,
            'Afonya\NSC\Register',
            'elementAdd'
        );

        // Регистрируем событие - изменение элемента
        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementUpdate',
            $this->MODULE_ID,
            'Afonya\NSC\Register',
            'elementUpdate'
        );

        // Регистрируем событие - удаление элемента
        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementDelete',
            $this->MODULE_ID,
            'Afonya\NSC\Register',
            'elementDel'
        );
    }

    public function doUninstall()
    {

        global $APPLICATION;

        $this->uninstallFiles();
//        $this->uninstallDB();
        $this->uninstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->includeAdminFile(
            'Uninstall «Afonya News statistic collector»',
            __DIR__ . '/unstep.php'
        );

    }

    public function uninstallFiles()
    {
        // удаляем настройки нашего модуля
        Option::delete($this->MODULE_ID);
    }

    public function uninstallDB()
    {
        // Есть таблица? - удаляем
        if (Loader::includeModule($this->MODULE_ID)) {
            if (Application::getConnection()->isTableExists(EventsTable::getTableName())) {
                $connection = Application::getInstance()->getConnection();
                $connection->dropTable(EventsTable::getTableName());
            }
        }
    }

    public function uninstallEvents()
    {

        // удаляем обработчики событий
        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnAfterIBlockElementAdd',
            $this->MODULE_ID,
            'Afonya\NSC\Register',
            'elementAdd'
        );

        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnAfterIBlockElementUpdate',
            $this->MODULE_ID,
            'Afonya\NSC\Register',
            'elementUpdate'
        );

        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnAfterIBlockElementDelete',
            $this->MODULE_ID,
            'Afonya\NSC\Register',
            'elementDel'
        );
    }
}
