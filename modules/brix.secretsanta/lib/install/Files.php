<?php
namespace Brix\SecretSanta\Install;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

/**
 * Class Files
 * 
 * @package Brix\SecretSanta\Install
 */
class Files
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * @var array
     */
    private static $paths = ["images", "js"];
    private static $dir = ["mobileapp"];

    /**
     * Copying files when installing a module
     */
    public static function install()
    {
        $docRoot = Application::getDocumentRoot();
        $ext = str_replace(".", "-", self::MODULE_ID);

        foreach (self::$paths as $path) {
            CopyDirFiles($docRoot . "/bitrix/modules/" . self::MODULE_ID . "/install/" . $path . "/", $docRoot . "/bitrix/" . $path . "/" . $ext . "/", true, true);
        }

        foreach (self::$dir as $path) {
            CopyDirFiles($docRoot . "/bitrix/modules/" . self::MODULE_ID . "/install/" . $path . "/", $docRoot . "/bitrix/" . $path . "/", true, true);
        }
    }

    /**
     * Removing files when uninstalling a module
     */
    public static function uninstall()
    {
        $docRoot = Application::getDocumentRoot();
        $ext = str_replace(".", "-", self::MODULE_ID);

        foreach (self::$paths as $path) {
            Directory::deleteDirectory($docRoot . "/bitrix/" . $path . "/" . $ext);
        }

        foreach (self::$dir as $path) {
            Directory::deleteDirectory($docRoot . "/bitrix/" . $path . "/" . self::MODULE_ID);
        }
    }
}