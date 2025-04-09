<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->setFrameMode(true);

$APPLICATION->IncludeComponent(
    "brix:accesses.history",
    "",
    [
        "SEF_FOLDER" => $arResult["SEF_FOLDER"],
        "URL_DEFAULT" => $arResult["URL_TEMPLATES"]["history"],
        "URL_DETAIL" => $arResult["URL_TEMPLATES"]["detail"],
    ],
    $component
);
