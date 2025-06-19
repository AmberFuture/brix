<?php
namespace Brix\SecretSanta\Handlers;

use Brix\SecretSanta\Config;
use Brix\SecretSanta\Helpers\{Base, Notifications, User};
use Brix\SecretSanta\Tables\SecretSantaTable;

/**
 * Class Agent
 * 
 * @package Brix\SecretSanta\Handlers
 */
class Agent
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * Sending a notification about the start of registration in the game
     * 
     * @return string
     */
    public static function registrationNotice(): string
    {
        if (Base::isRegistration()) {
            Notifications::invitation(User::getActiveEmployees());
        }

        return "";
    }

    /**
     * Sending a notification about the start of the game
     * 
     * @return string
     */
    public static function startNotice(): string
    {
        if (Base::isNotMoreDayFromStart()) {
            SecretSantaTable::distributionOfPlayers();
        }

        return "";
    }

    /**
     * Sending an additional notification
     * 
     * @return string
     */
    public static function additionalNotice(): string
    {
        if (Base::isNotMoreDayFromNoticeDate()) {
            SecretSantaTable::notifyingPlayers();
            Config::delete("noticedate");
        }

        return "";
    }

    /**
     * Completing the game
     * 
     * @return string
     */
    public static function completion(): string
    {
        if (Base::isCompletion()) {
            SecretSantaTable::clearTable();

            foreach (["dateregistration", "datestart", "datecompletion", "noticedate"] as $name) {
                Config::delete($name);
            }
        }

        return "";
    }
}