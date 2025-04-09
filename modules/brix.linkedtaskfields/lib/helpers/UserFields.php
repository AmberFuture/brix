<?php
namespace Brix\Helpers;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\{UserFieldTable, UserFieldLangTable};

/**
* Class UserFields
*
* @package Brix\Helpers
*/
final class UserFields
{
    /**
     * Acceptable field types
     * 
     * @var array
     */
    private static $types = ["boolean", "date", "datetime", "employee", "enumeration", "string", "integer", "double"];
    private static $typesIblock = ["iblock_section", "iblock_element"];

    /**
     * Acceptable field type if there is a CRM module.
     * 
     * @var srting
     */
    private static $typeCrm = "crm";

    /**
     * Gets a list of user fields in issues
     * 
     * @param bool $restrict
     * @param array $arFilter
     * @param ?int $limit
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getListFields($restrict = true, array $arFilter = [], ?int $limit = null): array
    {
        $arSelect = $restrict ? ["USER_TYPE_ID", "MULTIPLE", "SETTINGS"] : [];
        $arSelect = array_merge($arSelect, ["ID", "FIELD_NAME", "MANDATORY", "LABEL" => "LANG.EDIT_FORM_LABEL"]);
        if ($restrict) {
            $searchTypes = self::$types;

            if (ModuleManager::isModuleInstalled("iblock")) {
                $searchTypes = array_merge($searchTypes, self::$typesIblock);
            }

            if (ModuleManager::isModuleInstalled("crm")) {
                $searchTypes = array_merge($searchTypes, [self::$typeCrm]);
            }

            $arFilter = array_merge($arFilter, ["USER_TYPE_ID" => $searchTypes]);
        } else {
            $notTypes = [];

            if (!ModuleManager::isModuleInstalled("iblock")) {
                $notTypes = self::$typesIblock;
            }

            if (!ModuleManager::isModuleInstalled("crm")) {
                $notTypes = array_merge($notTypes, [self::$typeCrm]);
            }

            if (!empty($notTypes)) {
                $arFilter = array_merge($arFilter, ["!USER_TYPE_ID" => $notTypes]);
            }
        }

        $arFilter["!FIELD_NAME"][] = "UF_TASK_WEBDAV_FILES";
        $arFilter = array_merge($arFilter, ["ENTITY_ID" => "TASKS_TASK", "!LANG.EDIT_FORM_LABEL" => false]);
        $arOrder = ["LANG.EDIT_FORM_LABEL" => "ASC"];

        $db = UserFieldTable::getList([
            "select" => $arSelect,
            "order" => $arOrder,
            "filter" => $arFilter,
            "runtime" => [
                new Reference(
                    "LANG",
                    UserFieldLangTable::class,
                    Join::on("this.ID", "ref.USER_FIELD_ID")->where("ref.LANGUAGE_ID", LANGUAGE_ID)
                )
            ],
            "limit" => $limit,
            "cache" => [
                "ttl" => 86400,
                "cache_joins" => true
            ]
        ])->fetchAll();

        return $db;
    }

    /**
     * Gets the values of the list fields
     * 
     * @param array $arFilter
     * @return array
     * [
     *      "USER_FIELD_ID" => [
     *          "ID" => [
     *              "VALUE" => VALUE,
     *              "SORT" => SORT,
     *              "DEF" => DEF,
     *              "XML_ID" => XML_ID,
     *          ]
     *      ]
     * ]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getEnumerations(array $arFilter = []): array
    {
        $arEnums = [];
        $obEnum = new \CUserFieldEnum;
        $rsEnum = $obEnum->GetList(["SORT" => "ASC"], $arFilter);

        while ($arEnum = $rsEnum->Fetch()) {
            $arEnums[$arEnum["ID"]] = [
                "ID" => $arEnum["ID"],
                "USER_FIELD_ID" => $arEnum["USER_FIELD_ID"],
                "VALUE" => $arEnum["VALUE"],
                "SORT" => $arEnum["SORT"]
            ];
        }

        return $arEnums;
    }
}

?>