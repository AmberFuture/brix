<?php
namespace Brix\SecretSanta\Handlers;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\{Loader, UserTable};
use Bitrix\Main\UI\Extension;
use Brix\SecretSanta\Helpers\{Base, Notifications};
use Brix\SecretSanta\Tables\SecretSantaTable;

/**
 * Class Main
 * 
 * @package Brix\SecretSanta\Handlers
 */
final class Main
{
    /**
     * Method for the OnProlog event
     * 
     * @return void
     */
    public static function onProlog(): void
    {
        if (
            Base::isModules() && Loader::IncludeModule("fileman") && 
            (Base::isRegistration() || Base::isStart())
        ) {
            $currentUser = CurrentUser::get()->getId();
            $engine = new \CComponentEngine();
            $pageUser = $engine->guessComponentPath(
                "/company/personal/user/",
                [
                    "default" => "#user_id#/"
                ],
                $variables,
                false
            );

            if (!empty($pageUser) && (int) $variables["user_id"] === (int) $currentUser) {
                Extension::load(["brix_secretsanta_profile"]);
            }
        }
    }

    /**
     * Method for the OnAfterUserAdd event
     * 
     * @param array $arFields
     * @return void
     */
    public static function onAfterUserAdd(&$arFields)
    {
        if ((int) $arFields["ID"] > 0 && array_key_exists("UF_DEPARTMENT", $arFields) && !empty($arFields["UF_DEPARTMENT"])) {
            Notifications::invitation([$arFields["ID"]]);
        }
    }

    /**
     * Method for the OnAfterUserUpdate event
     * 
     * @param array $arFields
     * @return void
     */
    public static function onAfterUserUpdate(&$arFields)
    {
        if (array_key_exists("ACTIVE", $arFields)) {
            $arDeps = array_key_exists("UF_DEPARTMENT", $arFields) ? $arFields["UF_DEPARTMENT"] : UserTable::getRow([
                "select" => ["ID", "UF_DEPARTMENT"],
                "filter" => ["ID" => (int) $arFields["ID"]]
            ])["UF_DEPARTMENT"];
            SecretSantaTable::updateActivePlayer((int) $arFields["ID"], $arFields["ACTIVE"], $arDeps);
        }
    }

    /**
     * Method for the OnUserDelete event
     * 
     * @param $userId
     * @return void
     */
    public static function onUserDelete($userId)
    {
        SecretSantaTable::clearDeletedUser((int) $userId);
    }
}