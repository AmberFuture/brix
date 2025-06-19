<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}
?>
<?= CAdminMessage::ShowNote(Loc::getMessage("BRIX_SECRETSANTA_MODULE_INSTALL"));?>