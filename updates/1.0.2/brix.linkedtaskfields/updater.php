<?php

use Bitrix\Main\Loader;

/**
 * @global CUpdater $updater
 */

$module = "brix.linkedtaskfields";

if (Loader::includeModule($module)) {
    $ext = str_replace(".", "-", $module);
    $updater->CopyFiles("install/js", "/bitrix/js/$ext");
}