<?php
namespace Brix\Helpers;

use Bitrix\Main\Localization\Loc;

/**
* Class Conditions
*
* @package Brix\Helpers
*/
final class Conditions
{
    /**
     * Defines an array of available conditions
     * 
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function get(): array
    {
        return [
            "FILL" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_FILL"),
                "FIELDS" => ["group", "tags", "date", "datetime", "iblock_section", "employee", "crm", "iblock_element", "enumeration", "string", "integer", "double"],
                "MULTIPLE" => ["N", "Y"]
            ],
            "IN" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_IN"),
                "FIELDS" => ["group", "iblock_section", "employee", "crm", "iblock_element", "enumeration", "string"],
                "MULTIPLE" => ["N"]
            ],
            "IN_NO" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_IN_NO"),
                "FIELDS" => ["group", "iblock_section", "employee", "crm", "iblock_element", "enumeration", "string"],
                "MULTIPLE" => ["N"]
            ],
            "KEEP" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_KEEP"),
                "FIELDS" => ["tags", "iblock_section", "employee", "crm", "iblock_element", "enumeration", "string"],
                "MULTIPLE" => ["Y"]
            ],
            "KEEP_NO" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_KEEP_NO"),
                "FIELDS" => ["tags", "iblock_section", "employee", "crm", "iblock_element", "enumeration", "string"],
                "MULTIPLE" => ["Y"]
            ],
            "SAME" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_SAME"),
                "FIELDS" => ["tags", "boolean", "iblock_section", "employee", "crm", "iblock_element", "enumeration", "string"],
                "MULTIPLE" => ["Y"]
            ],
            "SAME_NO" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_SAME_NO"),
                "FIELDS" => ["tags", "boolean", "iblock_section", "employee", "crm", "iblock_element", "enumeration", "string"],
                "MULTIPLE" => ["Y"]
            ],
            "DATE" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_DATE"),
                "FIELDS" => ["date", "datetime"],
                "MULTIPLE" => ["N", "Y"]
            ],
            "LESS" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_LESS"),
                "FIELDS" => ["integer", "double"],
                "MULTIPLE" => ["N", "Y"]
            ],
            "LESS_OR" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_LESS_OR"),
                "FIELDS" => ["integer", "double"],
                "MULTIPLE" => ["N", "Y"]
            ],
            "BIG" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_BIG"),
                "FIELDS" => ["integer", "double"],
                "MULTIPLE" => ["N", "Y"]
            ],
            "BIG_OR" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_BIG_OR"),
                "FIELDS" => ["integer", "double"],
                "MULTIPLE" => ["N", "Y"]
            ],
            "RANGE" => [
                "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_CONDITIONS_RANGE"),
                "FIELDS" => ["integer", "double"],
                "MULTIPLE" => ["N", "Y"]
            ]
        ];
    }
}

?>