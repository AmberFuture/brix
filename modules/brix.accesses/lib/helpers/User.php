<?php
namespace Brix\Helpers;

use Bitrix\Main\UserTable;

/**
 * Class UserGroup
 * 
 * @package Brix\Helpers
 */
class User
{
    /**
     * We get users and format the name according to the site settings
     *
     * @param array $arUsers
     * @return array
     **/
    public static function getUserFormated($arUsers): array
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
}