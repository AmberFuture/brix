<?php
use Bitrix\Main\{Application, Loader};
use Bitrix\Main\IO\Directory;

$moduleId = strtolower(basename(__DIR__));
$arClasses = [];

function getBrixSecretsantaFiles($path, $moduleId, &$arClasses) {
    $dir = new Directory(Application::getDocumentRoot() . "/bitrix/modules/{$moduleId}/{$path}");

    foreach ($dir->getChildren() as $child) {
        if ($child->isFile()) {
            $name =  $child->getName();
            $key = implode("\\", array_map("ucwords", explode("/", $path)));
            $key = str_replace("Lib", "Brix\\SecretSanta", $key) . "\\" . str_replace(".php", "", $name);
            $arClasses[$key] = $path . "/" . $name;
        } else {
            getBrixSecretsantaFiles($path . "/" . $child->getName(), $moduleId, $arClasses);
        }
    }
}

getBrixSecretsantaFiles("lib", $moduleId, $arClasses);

Loader::registerAutoLoadClasses(
    $moduleId,
    $arClasses
);

$ext = str_replace(".", "-", $moduleId);
$arExtetions = [
    "brix_secretsanta_profile" => [
        "js" => "/bitrix/js/{$ext}/profile.js",
        "use" => \CJSCore::USE_PUBLIC
    ]
];

foreach ($arExtetions as $id => $arExt) {
    \CJSCore::RegisterExt($id, $arExt);
}
?>