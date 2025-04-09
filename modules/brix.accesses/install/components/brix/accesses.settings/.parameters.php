<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    "PARAMETERS" => [
        "SEF_FOLDER" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("BRIX_ACCESSES_SETTINGS_COMPONENT_SEF_FOLDER"),
            "TYPE" => "STRING",
            "DEFAULT" => "/brix_accesses/",
        ]
    ]
];
