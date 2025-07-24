<?php

use Bitrix\Main\Loader;

/**
 * @global CUpdater $updater
 */

$module = "brix.linkedtaskfields";
$ext = str_replace(".", "-", $module);

if (Loader::includeModule($module)) {
    $updater->CopyFiles("install", "/bitrix/modules/{$module}/install");
    $updater->CopyFiles("lib", "/bitrix/modules/{$module}/lib");
    $updater->CopyFiles("install/js", "/bitrix/js/{$ext}");
}