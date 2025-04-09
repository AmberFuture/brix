<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(["ui.icons"]);

$this->setFrameMode(true);

$style = "";

if (!empty($arResult["ROW"]["USER_PHOTO_PATH"])) {
    $photo = $arResult["ROW"]["USER_PHOTO_PATH"];
    $style = " --ui-icon-service-bg-image: url('{$photo}');";
    $style = 'style="' . $style . '"';
}

$userId = $arResult["ROW"]["USER_ID"];
$userName = $arResult["ROW"]["USER_NAME"];
$user = !empty($arResult["ROW"]["USER_NAME"]) ? "<a class='brix-history-detail__link ui-icon-common-user' href='/company/personal/user/{$userId}/' {$style}>{$userName}</a>" : Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_TEMPLATE_USER_ID_DELETE", ["#ID#" => $userId]);

?>
<div class="brix-history-detail">
    <table class="brix-history-detail__table">
        <?if ($arResult["ROW"]["DATE_MODIFIED"]) {?>
            <tr class="brix-history-detail__row">
                <td class="brix-history-detail__column"><?= Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_TEMPLATE_DATE_MODIFIED") ?></td>
                <td class="brix-history-detail__column"><?= $arResult["ROW"]["DATE_MODIFIED"] ?></td>
            </tr>
        <?}?>
        <tr class="brix-history-detail__row">
            <td class="brix-history-detail__column"><?= Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_TEMPLATE_USER_ID") ?></td>
            <td class="brix-history-detail__column"><?= $user ?></td>
        </tr>
        <?if ($arResult["ROW"]["IBLOCK_NAME"]) {?>
            <tr class="brix-history-detail__row">
                <td class="brix-history-detail__column"><?= Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_TEMPLATE_IBLOCK_ID") ?></td>
                <td class="brix-history-detail__column"><?= $arResult["ROW"]["IBLOCK_NAME"] ?></td>
            </tr>
        <?}?>
        <?if ($arResult["ROW"]["TYPE"]) {?>
            <tr class="brix-history-detail__row">
                <td class="brix-history-detail__column"><?= Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_TEMPLATE_TYPE") ?></td>
                <td class="brix-history-detail__column"><?= $arResult["ROW"]["TYPE"] ?></td>
            </tr>
        <?}?>
        <?if ($arResult["ROW"]["DESCRIPTION"]) {?>
            <tr class="brix-history-detail__row">
                <td class="brix-history-detail__column"><?= Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_TEMPLATE_DESCRIPTION") ?></td>
                <td class="brix-history-detail__column"><?= $arResult["ROW"]["DESCRIPTION"] ?></td>
            </tr>
        <?}?>
    </table>    
</div>
