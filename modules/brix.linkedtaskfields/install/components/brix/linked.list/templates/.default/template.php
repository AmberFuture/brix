<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

/**
 * @var CMain $APPLICATION
 * @var string $templateFolder
 */

Extension::load(["brix_linked_modal", "ui.icons.b24"]);
$messages = $arResult["POPUP_DELETE"];
$messages["BRIX_LINKED_LIST_GRID_ID"] = $arResult["GRID_ID"];

$APPLICATION->IncludeComponent("bitrix:main.ui.grid", "", [
    "ACTION_PANEL" => $arResult["ACTION_PANEL"],
    "AJAX_ID" => \CAjax::getComponentID("bitrix:main.ui.grid", ".default", ""),
    "AJAX_MODE" => "Y",
    "AJAX_OPTION_HISTORY" => "N",
    "AJAX_OPTION_JUMP" => "N",
    "ALLOW_COLUMNS_SORT" => true,
    "ALLOW_COLUMNS_RESIZE" => true,
    "ALLOW_HORIZONTAL_SCROLL" => true,
    "ALLOW_PIN_HEADER" => true,
    "ALLOW_SORT" => true,
    "COLUMNS" => $arResult["COLUMNS"],
    "GRID_ID" => $arResult["GRID_ID"],
    "NAV_OBJECT" => $arResult["NAV_OBJECT"],
    "PAGE_SIZES" => $arResult["PAGE_SIZES"],
    "ROWS" => $arResult["GRID_ROWS"],
    "SHOW_ACTION_PANEL" => true,
    "SHOW_CHECK_ALL_CHECKBOXES" => true,
    "SHOW_GRID_SETTINGS_MENU" => true,
    "SHOW_NAVIGATION_PANEL" => true,
    "SHOW_PAGESIZE" => true,
    "SHOW_PAGINATION" => true,
    "SHOW_ROW_ACTIONS_MENU" => true,
    "SHOW_ROW_CHECKBOXES" => true,
    "SHOW_SELECTED_COUNTER" => false,
    "SHOW_TOTAL_COUNTER" => false,
    "TOTAL_ROWS_COUNT" => $arResult["TOTAL_ROWS_COUNT"]
]);
?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        "use strict";

        BX.message(<?= Json::encode($messages) ?>);
        BrixLinkedList.createModal();
    });
</script>