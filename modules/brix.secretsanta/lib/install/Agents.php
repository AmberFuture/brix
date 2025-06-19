<?php
namespace Brix\SecretSanta\Install;

/**
 * Class Agents
 * 
 * @package Brix\SecretSanta\Install
 */
class Agents
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * Removing files when uninstalling a module
     */
    public static function uninstall()
    {
        \CAgent::RemoveModuleAgents(self::MODULE_ID);
    }
}