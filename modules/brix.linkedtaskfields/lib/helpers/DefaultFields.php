<?php
namespace Brix\Helpers;

use Bitrix\Main\Localization\Loc;

/**
* Class DefaultFields
*
* @package Brix\Helpers
*/
final class DefaultFields
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
            "CREATED_BY" => [
                "FIELD_NAME" => "CREATED_BY",
                "LABEL" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_DEFAULTFIELDS_CREATED_BY"),
                "MANDATORY" => "Y",
                "MULTIPLE" => "N",
                "USER_TYPE_ID" => "employee"
            ],
            "RESPONSIBLE_ID" => [
                "FIELD_NAME" => "RESPONSIBLE_ID",
                "LABEL" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_DEFAULTFIELDS_RESPONSIBLE_ID"),
                "MANDATORY" => "Y",
                "MULTIPLE" => "N",
                "USER_TYPE_ID" => "employee"
            ],
            "DEADLINE" => [
                "FIELD_NAME" => "DEADLINE",
                "LABEL" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_DEFAULTFIELDS_DEADLINE"),
                "MANDATORY" => "N",
                "MULTIPLE" => "N",
                "USER_TYPE_ID" => "datetime"
            ],
            "GROUP_ID" => [
                "FIELD_NAME" => "GROUP_ID",
                "LABEL" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_DEFAULTFIELDS_GROUP_ID"),
                "MANDATORY" => "N",
                "MULTIPLE" => "N",
                "USER_TYPE_ID" => "group"
            ],
            "TAGS" => [
                "FIELD_NAME" => "TAGS",
                "LABEL" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_DEFAULTFIELDS_TAGS"),
                "MANDATORY" => "N",
                "MULTIPLE" => "Y",
                "USER_TYPE_ID" => "tags"
            ]
        ];
    }
}

?>