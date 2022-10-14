<?php
/*
 * Файл local/modules/afonya.nsc/options.php
 */

use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

// получаем идентификатор модуля
$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);

// подключаем наш модуль
Loader::includeModule($module_id);

// подключаем модуль для получения инфоблоков
Loader::includeModule("iblock");

// Получение инфоблоков новостей
$dbIBlockNews = CIBlock::GetList(
    array("sort" => "asc",),
    array("ACTIVE" => "Y", "TYPE" => 'news')
);

while ($arIBlockNews = $dbIBlockNews->Fetch()) {
    $arIblocks[$arIBlockNews["ID"]] = "[" . $arIBlockNews["ID"] . "] " . $arIBlockNews["NAME"];
}

/*
 * Параметры модуля со значениями по умолчанию
 */

$aTabs = array(
    array(
        // Первая вкладка «Настройки»
        'DIV' => 'edit1',
        'TAB' => 'Настройки',
        'TITLE' => 'Настройки',
        'OPTIONS' => array(
            'Настройки сборки данных',
            array(
                'news_block',
                'Отслеживаемый  блок:',
                null,
                array(
                    'selectbox',
                    $arIblocks
                )
            )
        )
    )
);

// Создаем форму для редактирвания параметров модуля
$tabControl = new CAdminTabControl(
    'tabControl',
    $aTabs
);

$tabControl->Begin();
?>

    <form action="<?= $APPLICATION->GetCurPage(); ?>?mid=<?= $module_id; ?>&lang=<?= LANGUAGE_ID; ?>" method="post">
        <?= bitrix_sessid_post(); ?>
        <?php
        foreach ($aTabs as $aTab) { // цикл по вкладкам
            if ($aTab['OPTIONS']) {
                $tabControl->BeginNextTab();
                __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
            }
        }
        $tabControl->Buttons();
        ?>
        <input type="submit" name="apply"
               value="применить" class="adm-btn-save"/>
        <input type="submit" name="default"
               value="сбросить"/>
    </form>

<?php
$tabControl->End();

// Обрабатываем данные после отправки формы
if ($request->isPost() && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) { // цикл по вкладкам
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption)) { // если это название секции
                continue;
            }
            if ($arOption['note']) { // если это примечание
                continue;
            }
            if ($request['apply']) { // сохраняем введенные настройки
                $optionValue = $request->getPost($arOption[0]);
                if ($arOption[0] == 'switch_on') {
                    if ($optionValue == '') {
                        $optionValue = 'N';
                    }
                }
                if ($arOption[0] == 'jquery_on') {
                    if ($optionValue == '') {
                        $optionValue = 'N';
                    }
                }
                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
            } elseif ($request['default']) { // устанавливаем по умолчанию
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }
    LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . $module_id . '&lang=' . LANGUAGE_ID);
}
