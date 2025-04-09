<?php
namespace Brix\Helpers;

use Bitrix\Intranet\Util;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\{Loader, ModuleManager, UserTable};

/**
 * Class User
 * 
 * @package Brix\Helpers
 */
final class User
{
    /**
     * We get users and format the name according to the site settings
     *
     * @param array $arUsers
     * @return array
     **/
    public static function getUserFormated(array $arUsers): array
    {
        $arResult = [];
        $formatName = \CSite::GetNameFormat();
        $formatName = str_replace("#", "", $formatName);
        $formated = explode(" ", $formatName);
        $userField = array_merge(["ID"], $formated);
        $arUsers = array_unique($arUsers);
        $dbUsers = UserTable::getList([
            "select" => $userField,
            "filter" => ["ID" => $arUsers]
        ])->fetchAll();
        
        foreach ($dbUsers as $user) {
            $name = "";
            
            foreach ($formated as $value) {
                $name .= " " . $user[$value];
            }
            
            $arResult[$user["ID"]] = trim($name);
        }
        
        return $arResult;
    }

    /**
     * Processes the list of users and departments
     * 
     * @param array $arList = ["U1", "D2", "DC16" ...]
     * @return array
     */
    public static function getUserDepartments(array $arList): array
    {
        $users = [];

        if (ModuleManager::isModuleInstalled("intranet")) {
            $arDep = [];
            $arInChildDep = [];

            foreach ($arList as $item) {
                if (str_starts_with($item, "DC")) {
                    $arInChildDep[] = (int) substr($item, 2);
                } elseif (str_starts_with($item, "D")) {
                    $arDep[] = (int) substr($item, 1);
                } elseif (str_starts_with($item, "U")) {
                    $users[] = $item;
                } else {
                    $arDep[] = (int) $item;
                }
            }

            if ($arInChildDep) {
                $users = array_merge($users, self::getUtilDep($arInChildDep));
            }

            if ($arDep) {
                $users = array_merge($users, self::getUtilDep($arDep, false));
            }

            $users = array_unique($users);
        }

        return $users;
    }

    /**
     * Gets a list of users of divisions
     * 
     * @param array $arDepartments
     * @param bool $recursive
     * @return array
     */
    protected static function getUtilDep(array $arDepartments, bool $recursive = true): array
    {
        $users = [];

        if (Loader::includeModule("intranet")) {
            $resUsers = Util::getDepartmentEmployees([
                "DEPARTMENTS" => $arDepartments,
                "RECURSIVE" => $recursive ? "Y" : "N",
                "SELECT" => ["ID"]
            ]);
            
            while ($arUser = $resUsers->fetch()) {
                $users[] = "U" . $arUser["ID"];
            }
        }

        return $users;
    }

    /**
     * Checking access to the page
     * 
     * @return bool
     */
    public static function isAccess()
    {
        return CurrentUser::get()->isAdmin();
    }
}
