<?php
namespace Brix\SecretSanta\Helpers;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Type\DateTime;
use Brix\SecretSanta\Config;

/**
 * Class Base
 * 
 * @package Brix\SecretSanta\Helpers
 */
final class Base
{
    /**
     * Checks for the necessary modules
     * 
     * @return bool
     */
    public static function isModules(): bool
    {
        return (ModuleManager::isModuleInstalled("intranet") && ModuleManager::isModuleInstalled("socialnetwork") && ModuleManager::isModuleInstalled("im") && ModuleManager::isModuleInstalled("fileman"));
    }

    /**
     * Checks for the mobile module
     * 
     * @return bool
     */
    public static function isModuleMobile(): bool
    {
        return ModuleManager::isModuleInstalled("mobile");
    }

    /**
     * Checks that the game has started, but the participants are not distributed
     * 
     * @return bool
     */
    public static function isRegistration(): bool
    {
        return (
            !empty(Config::dateregistration()) && 
            Config::dateregistration() <= strtotime(new DateTime()) && 
            (empty(Config::datestart()) || Config::datestart() > strtotime(new DateTime()))
        );
    }

    /**
     * Verifies that the participants are distributed
     * 
     * @return bool
     */
    public static function isStart(): bool
    {
        return (
            !empty(Config::dateregistration()) && !empty(Config::datestart()) && 
            Config::dateregistration() < strtotime(new DateTime()) && 
            Config::datestart() <= strtotime(new DateTime()) && 
            (empty(Config::datecompletion()) || Config::datecompletion() > strtotime(new DateTime()))
        );
    }

    /**
     * Checks that no more than 1 day has passed since the start.
     * 
     * @return bool
     */
    public static function isNotMoreDayFromStart(): bool
    {
        if (self::isStart()) {
            $moreOneDay = DateTime::createFromTimestamp(Config::datestart())->add("+1 day");

            return (strtotime($moreOneDay) >= strtotime(new DateTime()));
        }

        return false;
    }

    /**
     * Checks that no more than 1 day has passed since the start.
     * 
     * @return bool
     */
    public static function isNotMoreDayFromNoticeDate(): bool
    {
        if (
            !empty(Config::dateregistration()) && !empty(Config::datestart()) && !empty(Config::noticedate()) && 
            Config::dateregistration() < strtotime(new DateTime()) && 
            (empty(Config::datecompletion()) || Config::datecompletion() > strtotime(new DateTime()))
        ) {
            $moreOneDay = DateTime::createFromTimestamp(Config::noticedate())->add("+1 day");

            return (strtotime($moreOneDay) >= strtotime(new DateTime()));
        }

        return false;
    }

    /**
     * The game is over
     * 
     * @return bool
     */
    public static function isCompletion(): bool
    {
        return (!empty(Config::datecompletion()) && Config::datecompletion() <= strtotime(new DateTime()));
    }
}
