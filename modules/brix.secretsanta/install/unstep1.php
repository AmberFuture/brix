<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
global $APPLICATION;

if (!check_bitrix_sessid()) {
    return;
}
?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="hidden" name="id" value="brix.secretsanta">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?= CAdminMessage::ShowMessage(Loc::getMessage("BRIX_SECRETSANTA_UNINSTALL_WARN"))?>
    <p>
        <input type="checkbox" name="save_options" id="save_options" value="Y">
        <label for="save_options"><?= Loc::getMessage("BRIX_SECRETSANTA_UNINSTALL_SAVE_OPTIONS")?></label>
    </p>
    <p>
        <input type="checkbox" name="save_tables" id="save_tables" value="Y">
        <label for="save_tables"><?= Loc::getMessage("BRIX_SECRETSANTA_UNINSTALL_SAVE_TABLES")?></label>
    </p>
    <input type="submit" name="inst" value="<?= Loc::getMessage("BRIX_SECRETSANTA_UNINSTALL_DEL")?>">
</form>
