<?php
/*
 * Файл local/modules/afonya.nsc/install/unstep.php
 */

if (!check_bitrix_sessid()) {
    return;
}

if ($errorException = $APPLICATION->getException()) {
    // ошибка при удалении модуля
    CAdminMessage::showMessage(
        'Uninstall error' . ': ' . $errorException->GetString()
    );
} else {
    // модуль успешно удален
    CAdminMessage:
    showNote(
        'Uninstall success'
    );
}
?>

<form action="<?= $APPLICATION->getCurPage(); ?>"> <!-- Кнопка возврата к списку модулей -->
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>"/>
    <input type="submit" value="К списку модулей">
</form>