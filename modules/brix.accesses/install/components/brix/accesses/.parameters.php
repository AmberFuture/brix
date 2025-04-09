<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    "PARAMETERS" => [
        "SEF_MODE" => [
            "history" => [
                "NAME" => Loc::getMessage("BRIX_ACCESSES_PARAMS_HISTORY"),
                "DEFAULT" => "history/",
                "VARIABLES" => [],
            ]
        ]
    ]
];
