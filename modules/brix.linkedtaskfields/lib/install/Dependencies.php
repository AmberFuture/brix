<?php
namespace Brix\Install;

use Bitrix\Main\EventManager;

/**
 * Class Dependencies
 * 
 * @package Brix\Install
 */
class Dependencies
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.linkedtaskfields";

    /**
     * Array of processing to be created
     * 
     * @return array
     * [
     *  [
     *      "fromModuleId" => "",
     *      "eventType" => "",
     *      "toClass" => "",
     *      "toMethod" => ""
     *  ]
     * ]
     */
    private static function getHandlers(): array
    {
        return [
            [
                "fromModuleId" => "main",
                "eventType" => "OnProlog",
                "toClass" => "Brix\Handlers\Main",
                "toMethod" => "onProlog"
            ],
            [
                "fromModuleId" => "tasks",
                "eventType" => "OnBeforeTaskAdd",
                "toClass" => "Brix\Handlers\Tasks",
                "toMethod" => "onBeforeTaskAdd"
            ],
            [
                "fromModuleId" => "tasks",
                "eventType" => "OnBeforeTaskUpdate",
                "toClass" => "Brix\Handlers\Tasks",
                "toMethod" => "onBeforeTaskUpdate"
            ]
        ];
    }

    /**
     * Registering our handlers
     * 
     * @return void;
     */
    public static function install() {
        $eManager = EventManager::getInstance();

        foreach (self::getHandlers() as $handler) {
            $eManager->registerEventHandler($handler["fromModuleId"], $handler["eventType"], self::MODULE_ID, $handler["toClass"], $handler["toMethod"], 99999);
        }
    }

    /**
     * Removing our handlers
     * 
     * @return void;
     */
    public static function uninstall() {
        $eManager = EventManager::getInstance();

        foreach (self::getHandlers() as $handler) {
            $eManager->unRegisterEventHandler($handler["fromModuleId"], $handler["eventType"], self::MODULE_ID, $handler["toClass"], $handler["toMethod"]);
        }
    }
}