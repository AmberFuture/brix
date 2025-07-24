<?php

use Bitrix\Main\Loader;

/**
 * @global CUpdater $updater
 */

$module = "brix.accesses";

if (Loader::includeModule($module)) {
    $updater->CopyFiles("install/components", "/bitrix/components");
}