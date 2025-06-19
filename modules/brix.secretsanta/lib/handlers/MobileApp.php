<?php
namespace Brix\SecretSanta\Handlers;

/**
 * Class MobileApp
 * 
 * @package Brix\SecretSanta\Handlers
 */
final class MobileApp
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * Method for the onJNComponentWorkspaceGet event
     * Returns the path from the root directory to mobile app workspace
     *
     * @return string
     */
    public static function onJNComponentWorkspaceGet(): string
    {
        return "/bitrix/mobileapp/" . self::MODULE_ID . "/";
    }
}