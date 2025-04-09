<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    "PARAMETERS" => [
        "FIELD_NAME" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_PARAMS_FIELD_NAME"),
            "TYPE" => "STRING",
            "DEFAULT" => ""
        ]
    ]
];
