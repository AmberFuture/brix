<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$APPLICATION->IncludeComponent(
    "bitrix:ui.sidepanel.wrapper",
    "",
    [
        "POPUP_COMPONENT_NAME" => "brix:accesses.history.detail",
        "POPUP_COMPONENT_TEMPLATE_NAME" => "",
        "POPUP_COMPONENT_PARAMS" => [
            "SEF_FOLDER" => $arResult["SEF_FOLDER"],
            "URL_DEFAULT" => $arResult["URL_TEMPLATES"]["history"],
            "URL_DETAIL" => $arResult["URL_TEMPLATES"]["detail"],
            "ID" => $arResult["VARIABLES"]["ID"] ?? 0
        ],
        "USE_BACKGROUND_CONTENT" => false,
        "USE_UI_TOOLBAR" => "Y",
        "PAGE_MODE" => false,
        "USE_PADDING" => false,
        "PAGE_MODE_OFF_BACK_URL" => $arResult["SEF_FOLDER"] . $arResult["URL_TEMPLATES"]["history"]
    ],
    $component
);