<?php
/*
 * Файл local/modules/afonya.nsc/install/step.php
 */

if (!check_bitrix_sessid()) {
    return;
}

if ($errorException = $APPLICATION->getException()) {
    // ошибка при установке модуля
    CAdminMessage::showMessage(
        'Afonya NSC install failed' . ': ' . $errorException->GetString()
    );
} else {
    // модуль успешно установлен
    CAdminMessage::showNote(
        'Afonya NSC install success'
    );
}
