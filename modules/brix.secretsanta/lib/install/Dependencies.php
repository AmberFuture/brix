<?php
namespace Brix\SecretSanta\Install;

use Bitrix\Main\EventManager;

/**
 * Class Dependencies
 * 
 * @package Brix\SecretSanta\Install
 */
class Dependencies
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

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
                "fromModuleId" => "im",
                "eventType" => "OnBeforeConfirmNotify",
                "toClass" => "\Brix\SecretSanta\Handlers\Im",
                "toMethod" => "onBeforeConfirmNotify"
            ],
            [
                "fromModuleId" => "main",
                "eventType" => "OnProlog",
                "toClass" => "\Brix\SecretSanta\Handlers\Main",
                "toMethod" => "onProlog"
            ],
            [
                "fromModuleId" => "main",
                "eventType" => "OnAfterUserAdd",
                "toClass" => "\Brix\SecretSanta\Handlers\Main",
                "toMethod" => "onAfterUserAdd"
            ],
            [
                "fromModuleId" => "main",
                "eventType" => "OnAfterUserUpdate",
                "toClass" => "\Brix\SecretSanta\Handlers\Main",
                "toMethod" => "onAfterUserUpdate"
            ],
            [
                "fromModuleId" => "main",
                "eventType" => "OnUserDelete",
                "toClass" => "\Brix\SecretSanta\Handlers\Main",
                "toMethod" => "onUserDelete"
            ],
            [
                "fromModuleId" => "mobile",
                "eventType" => "onMobileMenuStructureBuilt",
                "toClass" => "\Brix\SecretSanta\Handlers\Mobile",
                "toMethod" => "onMobileMenuStructureBuilt"
            ],
            [
                "fromModuleId" => "mobileapp",
                "eventType" => "onJNComponentWorkspaceGet",
                "toClass" => "\Brix\SecretSanta\Handlers\MobileApp",
                "toMethod" => "onJNComponentWorkspaceGet"
            ],
            [
                "fromModuleId" => "pull",
                "eventType" => "OnGetDependentModule",
                "toClass" => "\Brix\SecretSanta\Handlers\Pull",
                "toMethod" => "onGetDependentModule"
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