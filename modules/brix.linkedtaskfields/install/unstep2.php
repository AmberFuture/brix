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
<?php
if ($ex = $APPLICATION->GetException()) {
    echo CAdminMessage::ShowMessage([
        "TYPE" => "ERROR",
        "MESSAGE" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_MOD_UNINST_ERR"),
        "DETAILS" => $ex->GetString(),
        "HTML" => true,
    ]);
} else {
    echo CAdminMessage::ShowNote(Loc::getMessage("BRIX_LINKEDTASKFIELDS_MOD_UNINST_OK"));
}
?>
<form action="<?= $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?= LANG ?>">
    <input type="submit" name="" value="<?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_MOD_BACK")?>">
<form>