<?php
namespace Brix\SecretSanta\Handlers;

/**
 * Class Pull
 * 
 * @package Brix\SecretSanta\Handlers
 */
final class Pull
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * OnGetDependentModule handler
     * Registers the module in the push and pull module
     * 
     * @return array
     */
    public static function onGetDependentModule(): array
    {
        return [
            "MODULE_ID" => self::MODULE_ID,
            "USE" => ["PUBLIC_SECTION"]
        ];
    }
}