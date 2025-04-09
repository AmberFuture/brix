<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

$moduleId = "brix.accesses";
$docRoot = Application::getDocumentRoot();

Loc::loadMessages("{$docRoot}/bitrix/modules/{$moduleId}/lib/install/LeftMenu.php");

$aMenuLinks = [
    [
		Loc::getMessage("BRIX_ACCESSES_LEFT_MENU_START_NAME"), 
		"/brix_accesses/", 
		[], 
		[], 
		"\Bitrix\Main\Loader::includeModule('brix.accesses') && \Brix\Helpers\Access::getAccess()"
    ],
    [
		Loc::getMessage("BRIX_ACCESSES_LEFT_MENU_HISTORY_NAME"), 
		"/brix_accesses/history/", 
		[], 
		[], 
		"\Bitrix\Main\Loader::includeModule('brix.accesses') && \Brix\Helpers\Access::getAccess('history')" 
    ]
];

?>