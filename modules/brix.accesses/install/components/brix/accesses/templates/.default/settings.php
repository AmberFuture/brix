<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$APPLICATION->IncludeComponent(
    "brix:accesses.settings",
    "",
    [
        "SEF_FOLDER" => $arResult["SEF_FOLDER"],
    ],
    $component
);
