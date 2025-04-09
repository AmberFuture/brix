<?php
use Bitrix\Main\{Application, Loader};
use Bitrix\Main\IO\Directory;

$moduleId = strtolower(basename(__DIR__));
$arClasses = [];

function getFiles($path, $moduleId, &$arClasses) {
    $dir = new Directory(Application::getDocumentRoot() . "/bitrix/modules/{$moduleId}/{$path}");

    foreach ($dir->getChildren() as $child) {
        if ($child->isFile()) {
            $name =  $child->getName();
            $key = implode("\\", array_map("ucwords", explode("/", $path)));
            $key = str_replace("Lib", "Brix", $key) . "\\" . str_replace(".php", "", $name);
            $arClasses[$key] = $path . "/" . $name;
        } else {
            getFiles($path . "/" . $child->getName(), $moduleId, $arClasses);
        }
    }
}

getFiles("lib", $moduleId, $arClasses);

Loader::registerAutoLoadClasses(
    $moduleId,
    $arClasses
);

$ext = str_replace(".", "-", $moduleId);
$arExtetions = [
    "brix_access" => [
        "js" => "/bitrix/js/{$ext}/access.js",
        "css" => "/bitrix/css/{$ext}/access.css",
        "lang" => "/bitrix/modules/{$moduleId}/lang/" . LANGUAGE_ID . "/js/access.php",
        "rel" => ["ui.select", "access"],
        "use" => \CJSCore::USE_PUBLIC
    ],
    "brix_modal" => [
        "js" => "/bitrix/js/{$ext}/modal.js",
        "css" => "/bitrix/css/{$ext}/modal.css",
        "rel" => ["ui.buttons"],
        "use" => \CJSCore::USE_PUBLIC
    ]
];

foreach ($arExtetions as $id => $arExt) {
    \CJSCore::RegisterExt($id, $arExt);
}
?>