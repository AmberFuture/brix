<?php
namespace Brix\Install;

use Bitrix\Main\Type\DateTime;

/**
 * Class Files
 * 
 * @package Brix\Install
 */
class Agents
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.accesses";

    /**
     * Array of processing to be created
     * 
     * @return \string[][]
     */
    private static function getAgents()
    {
        $dateTimeFormat = DateTime::getFormat();
        return [
            [
                "function" => "\Brix\Handlers\Agent::historyClear();",
                "interval" => 24*60*60,
                "date" => date($dateTimeFormat, strtotime("+1 day", strtotime(date("d.m.Y 01:00:00"))))
            ]
        ];
    }

    /**
     * Copying files when installing a module
     */
    public static function install()
    {
        foreach (self::getAgents() as $agent) {
            \CAgent::AddAgent(
                $agent["function"],
                self::MODULE_ID,
                "Y",
                $agent["interval"],
                $agent["date"],
                "Y",
                $agent["date"]
            );
        }
    }

    /**
     * Removing files when uninstalling a module
     */
    public static function uninstall()
    {
        foreach (self::getAgents() as $agent) {
            \CAgent::RemoveAgent($agent["function"], self::MODULE_ID);
        }
    }
}