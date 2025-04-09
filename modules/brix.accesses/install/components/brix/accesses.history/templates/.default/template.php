<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var CMain $APPLICATION
 * @var string $templateFolder
 */

$this->SetViewTarget("inside_pagetitle");
$APPLICATION->IncludeComponent(
    "bitrix:main.ui.filter",
    "",
    [
        "FILTER_ID" => $arResult["FILTER_ID"],
        "GRID_ID" => $arResult["GRID_ID"],
        "FILTER" => $arResult["FILTER"],
        "ENABLE_LIVE_SEARCH" => true,
        "ENABLE_LABEL" => true
    ]
);
$this->EndViewTarget();
$this->setFrameMode(true);
$APPLICATION->IncludeComponent("bitrix:main.ui.grid", "", [
    "GRID_ID" => $arResult["GRID_ID"],
    "COLUMNS" => $arResult["COLUMNS"],
    "ROWS" => $arResult["GRID_ROWS"],
    "SHOW_ROW_CHECKBOXES" => false,
    "NAV_OBJECT" => $arResult["NAV_OBJECT"],
    "AJAX_MODE" => "Y",
    "AJAX_ID" => \CAjax::getComponentID("bitrix:main.ui.grid", ".default", ""),
    "PAGE_SIZES" => $arResult["PAGE_SIZES"],
    "AJAX_OPTION_JUMP" => "N",
    "SHOW_CHECK_ALL_CHECKBOXES" => false,
    "SHOW_ROW_ACTIONS_MENU" => true,
    "SHOW_GRID_SETTINGS_MENU" => true,
    "SHOW_NAVIGATION_PANEL" => true,
    "SHOW_PAGINATION" => true,
    "SHOW_SELECTED_COUNTER" => false,
    "SHOW_TOTAL_COUNTER" => false,
    "SHOW_PAGESIZE" => true,
    "SHOW_ACTION_PANEL" => true,
    "ALLOW_COLUMNS_SORT" => true,
    "ALLOW_COLUMNS_RESIZE" => true,
    "ALLOW_HORIZONTAL_SCROLL" => true,
    "ALLOW_SORT" => true,
    "ALLOW_PIN_HEADER" => true,
    "AJAX_OPTION_HISTORY" => "N",
    "ACTION_PANEL" => []
]);