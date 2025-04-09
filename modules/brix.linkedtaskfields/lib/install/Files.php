<?php
namespace Brix\Install;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

/**
 * Class Files
 * 
 * @package Brix\Install
 */
class Files
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.linkedtaskfields";

    /**
     * @var array
     */
    private static $PATHS = ["css", "js", "tools"];

    /**
     * Copying files when installing a module
     */
    public static function install()
    {
        $docRoot = Application::getDocumentRoot();
        $ext = str_replace(".", "-", self::MODULE_ID);

        foreach (self::$PATHS as $path) {
            CopyDirFiles($docRoot . "/bitrix/modules/" . self::MODULE_ID . "/install/" . $path . "/", $docRoot . "/bitrix/" . $path . "/" . $ext . "/", true, true);
        }

        CopyDirFiles($docRoot . "/bitrix/modules/" . self::MODULE_ID . "/install/components/", $docRoot . "/bitrix/components/", true, true);
    }

    /**
     * Removing files when uninstalling a module
     */
    public static function uninstall()
    {
        $docRoot = Application::getDocumentRoot();
        $ext = str_replace(".", "-", self::MODULE_ID);

        foreach (self::$PATHS as $path) {
            Directory::deleteDirectory($docRoot . "/bitrix/" . $path . "/" . $ext);
        }
        
        $componentsDir = new Directory($docRoot . "/bitrix/modules/" . self::MODULE_ID . "/install/components/brix/");

        if ($componentsDir->isExists()) {
            $children = $componentsDir->getChildren();

            if ($children) {
                foreach ($children as $child) {
                    $cl = $child::class;

                    if (str_ends_with($cl, "Directory")) {
                        $name = $child->getName();
                        Directory::deleteDirectory($docRoot . "/bitrix/components/brix/" . $name);
                    }
                }
            }
        }

        $componentsDir = new Directory($docRoot . "/bitrix/components/brix/");

        if ($componentsDir->isExists()) {
            $children = $componentsDir->getChildren();

            if (!$children) {
                $componentsDir->delete();
            }
        }
    }
}