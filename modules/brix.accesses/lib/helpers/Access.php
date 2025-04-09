<?php
namespace Brix\Helpers;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Brix\Config;
use Brix\Helpers\UserGroup;

/**
 * Class Access
 * 
 * @package Brix\Helpers
 */
class Access
{
    /**
     * Returns a list of available rights/options with rights
     * 
     * @return array
     */
    public static function getRights(): array
    {
        return [
            "viewing" => Loc::getMessage("BRIX_ACCESSES_RIGHTS_LIST_VIEWING"),
            "vhistory" => Loc::getMessage("BRIX_ACCESSES_RIGHTS_LIST_VHISTORY"),
            "changing" => Loc::getMessage("BRIX_ACCESSES_RIGHTS_LIST_CHANGING")
        ];
    }

    /**
     * Checks access rights
     * 
     * @param string $access (view, change, history)
     * @return bool
     */
    public static function getAccess($access = "view"): bool
    {
        $user = CurrentUser::get();
        $userId = $user->getId();

        if (!$userId) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $method = ($access === "change") ? "changing" : (($access === "history") ? "vhistory" : "viewing");
        $arGroupOption = Config::$method();

        if (!$arGroupOption) {
            return false;
        }

        $arUserGroup = UserGroup::getUserGroup((int) $userId);

        if ($arUserGroup && array_intersect($arGroupOption, $arUserGroup)) {
            return true;
        }

        return false;
    }
}