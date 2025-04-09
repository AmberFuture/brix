<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$moduleId = "brix.accesses";
$docRoot = Application::getDocumentRoot();

if (!Loader::includeModule($moduleId)) {
    return [];
}

Loc::loadMessages("{$docRoot}/bitrix/modules/{$moduleId}/lib/api/providers/IblockProvider.php");

return [
    "settings" => [
        "entities" => [
            [
                "id" => "brix_iblocks",
                "options" => [
                    "dynamicLoad" => true,
                    "dynamicSearch" => true,
                    "itemOptions" => [
                        "inactive" => [
                            "badges" => [
                                [
                                    "title" => Loc::getMessage("BRIX_ACCESSES_IBLOCK_PROVIDER_TAB_TITLE_INACTIVE"),
                                    "textColor" => "#484e53",
                                    "bgColor" => "#eaebec"
                                ]
                            ]
                        ]
                    ],
                    "tagOptions" => [
                        "default" => [
                            "textColor" => "#474d54",
                            "bgColor" => "rgb(223 235 255 / 80%)"
                        ],
                        "inactive" => [
                            "textColor" => "#494f56",
                            "bgColor" => "#ecedef"
                        ]
                    ]
                ]
            ]
        ]
    ]
];
