<?php
use Bitrix\Main\{Application, Loader};
use Bitrix\Main\IO\Directory;

$moduleId = strtolower(basename(__DIR__));
$arClasses = [];

function getBrixLinkedtaskfieldsFiles($path, $moduleId, &$arClasses) {
    $dir = new Directory(Application::getDocumentRoot() . "/bitrix/modules/{$moduleId}/{$path}");

    foreach ($dir->getChildren() as $child) {
        if ($child->isFile()) {
            $name =  $child->getName();
            $key = implode("\\", array_map("ucwords", explode("/", $path)));
            $key = str_replace("Lib", "Brix", $key) . "\\" . str_replace(".php", "", $name);
            $arClasses[$key] = $path . "/" . $name;
        } else {
            getBrixLinkedtaskfieldsFiles($path . "/" . $child->getName(), $moduleId, $arClasses);
        }
    }
}

getBrixLinkedtaskfieldsFiles("lib", $moduleId, $arClasses);

Loader::registerAutoLoadClasses(
    $moduleId,
    $arClasses
);

$ext = str_replace(".", "-", $moduleId);
$arExtetions = [
    "brix_linked_task_settings" => [
        "js" => "/bitrix/js/{$ext}/settings.js",
        "lang" => "/bitrix/modules/{$moduleId}/lang/" . LANGUAGE_ID . "/js/settings.php",
        "use" => \CJSCore::USE_PUBLIC
    ],
    "brix_linked_task_edit" => [
        "js" => "/bitrix/js/{$ext}/edit.js",
        "use" => \CJSCore::USE_PUBLIC
    ],
    "brix_linked_modal" => [
        "js" => "/bitrix/js/{$ext}/modal.js",
        "rel" => ["ui.buttons"],
        "use" => \CJSCore::USE_PUBLIC
    ],
    "brix_linked_modal_css" => [
        "css" => "/bitrix/css/{$ext}/modal.css",
        "rel" => ["ui.buttons"],
        "use" => \CJSCore::USE_PUBLIC
    ]
];

foreach ($arExtetions as $id => $arExt) {
    \CJSCore::RegisterExt($id, $arExt);
}
?>