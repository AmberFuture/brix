<?php
namespace Brix\SecretSanta\Helpers;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UserTable;

/**
 * Class User
 * 
 * @package Brix\SecretSanta\Helpers
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
     * Checks whether the user is an employee
     * 
     * @param ?int $userId
     * @return bool
     */
    public static function isEmployee(?int $userId = null): bool
    {
        if (!$userId) {
            $userId = CurrentUser::get()->getId();
        }

        $employee = false;

        if ($userId) {
            $db = UserTable::getRow([
                "select" => ["ID"],
                "filter" => ["ID" => (int) $userId, "!UF_DEPARTMENT" => false]
            ]);

            $employee = $db ? true : false;
        }
        
        return $employee;
    }

    /**
     * Returns an array of active employees
     * 
     * @return array
     */
    public Static function getActiveEmployees(): array
    {
        $db = UserTable::getList([
            "select" => ["ID"],
            "filter" => ["ACTIVE" => "Y", "!UF_DEPARTMENT" => false]
        ])->fetchAll();

        return array_column($db, "ID");
    }
}
