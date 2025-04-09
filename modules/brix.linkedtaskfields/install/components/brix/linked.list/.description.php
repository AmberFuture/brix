<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_NAME"),
    "DESCRIPTION" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_DESCR"),
    "PATH" => [
        "ID" => "brix",
        "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_PATH_NAME")
    ]
];
