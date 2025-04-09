<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

$moduleId = "brix.linkedtaskfields";
$docRoot = Application::getDocumentRoot();

if (!Loader::includeModule($moduleId)) {
    return [];
}

return [
    "settings" => [
        "entities" => [
            [
                "id" => "brix_task_tag",
                "options" => [
                    "dynamicLoad" => true,
                    "dynamicSearch" => true,
                    "itemOptions" => [
                        "default" => [
                            "avatar" => "/bitrix/js/tasks/entity-selector/src/images/default-tag.svg",
                            "badgesOptions" => [
                                "fitContent" => true,
                                "maxWidth" => 100
                            ]
                        ]
                    ]
                ],
                "id" => "brix_enumeration",
                "options" => [
                    "dynamicLoad" => true,
                    "dynamicSearch" => true
                ]
            ]
        ]
    ]
];
