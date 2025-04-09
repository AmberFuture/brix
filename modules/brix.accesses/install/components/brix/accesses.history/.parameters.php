<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    "PARAMETERS" => [
        "SEF_FOLDER" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_SEF_FOLDER"),
            "TYPE" => "STRING",
            "DEFAULT" => "/brix_accesses/"
        ],
        "URL_DEFAULT" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_URL_DEFAULT"),
            "TYPE" => "STRING",
            "DEFAULT" => "history/"
        ],
        "URL_DETAIL" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_URL_DETAIL"),
            "TYPE" => "STRING",
            "DEFAULT" => "history/#ID#/"
        ]
    ]
];
