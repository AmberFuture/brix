<?php
namespace Brix\SecretSanta\Handlers;

use Bitrix\Main\Loader;
use Brix\SecretSanta\Helpers\Notifications;
use Brix\SecretSanta\Tables\SecretSantaTable;

/**
 * Class Im
 * 
 * @package Brix\SecretSanta\Handlers
 */
final class Im
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * Method for the OnBeforeConfirmNotify event
     * 
     * @param string $module
     * @param string $tag
     * @param string $value
     * @param mixed $notify
     * @return array
     */
    public static function onBeforeConfirmNotify($module, $tag, $value, $notify)
    {
        if ($module === self::MODULE_ID && Loader::includeModule(self::MODULE_ID) && Loader::includeModule("im")) {
            $arTag = explode("|", $tag);
            $userId = (int) end($arTag);

            $player = SecretSantaTable::createObject();
            $player->setPlayer($userId);

            if ($value === "Y") {
                $player->setTakePart("Y");
            }

            $player->fill();
            $player->save();

            if ($value === "Y") {
                Notifications::wish($userId);
            }

            \CIMNotify::DeleteByTag($tag);

            return true;
        }
    }
}