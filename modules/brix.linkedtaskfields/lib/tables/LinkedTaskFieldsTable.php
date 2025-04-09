<?php
namespace Brix\Tables;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\{BooleanField, DatetimeField, IntegerField, StringField, TextField};
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\{UserFieldTable, UserFieldLangTable, UserTable};

/**
* Class LinkedTaskFieldsTable
*
* @package Brix\Tables
*/
class LinkedTaskFieldsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     * 
     * @return string
     */
    public static function getTableName()
    {
        return "brix_linked_task_fields";
    }
    
    /**
     * Returns entity map definition.
     * 
     * @return array
     */
    public static function getMap()
    {
        return [
            new StringField("FIELD_NAME", [
                "primary" => true,
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_FIELD_NAME")
            ]),
            new BooleanField("ACTIVE", [
                "values" => ["N", "Y"],
                "default_value" => "Y",
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_ACTIVE")
            ]),
            new DatetimeField("DATE_CREATE", [
                "default_value" => function()
                {
                    return new DateTime();
                },
                "fetch_data_modification" => function()
                {
                    return [
                        function($value)
                        {
                            return self::dateFormatted($value);
                        }
                    ];
                },
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_DATE_CREATE")
            ]),
            new IntegerField("CREATED_BY", [
                "default_value" => function()
                {
                    return CurrentUser::get()->getId();
                },
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_CREATED_BY")
            ]),
            new DatetimeField("TIMESTAMP_X", [
                "default_value" => function()
                {
                    return new DateTime();
                },
                "fetch_data_modification" => function()
                {
                    return [
                        function($value)
                        {
                            return self::dateFormatted($value);
                        }
                    ];
                },
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_TIMESTAMP_X")
            ]),
            new IntegerField("MODIFIED_BY", [
                "default_value" => function()
                {
                    return CurrentUser::get()->getId();
                },
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_MODIFIED_BY")
            ]),
            new BooleanField("REQUIRED", [
                "values" => ["N", "Y"],
                "default_value" => "N",
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_REQUIRED")
            ]),
            new TextField("CONDITIONS", [
                "save_data_modification" => function()
                {
                    return [
                        function($value)
                        {
                            return serialize($value);
                        }
                    ];
                },
                "fetch_data_modification" => function()
                {
                    return [
                        function($value)
                        {
                            return unserialize($value);
                        }
                    ];
                },
                "title" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_TABLE_CONDITIONS")
            ]),
            new Reference(
                "CREATED_BY_USER",
                UserTable::class,
                Join::on("this.CREATED_BY", "ref.ID")
            ),
            new Reference(
                "MODIFIED_BY_USER",
                UserTable::class,
                Join::on("this.MODIFIED_BY", "ref.ID")
            ),
            new Reference(
                "FIELD",
                UserFieldTable::class,
                Join::on("this.FIELD_NAME", "ref.FIELD_NAME")->where("ref.ENTITY_ID", "TASKS_TASK")
            ),
            new Reference(
                "LANG",
                UserFieldLangTable::class,
                Join::on("this.FIELD.ID", "ref.USER_FIELD_ID")->where("ref.LANGUAGE_ID", LANGUAGE_ID)
            )
        ];
    }

    /**
     * Converts the date to a string in the website format
     * 
     * @param DateTime $datetime
     * @return string
     */
    private static function dateFormatted(DateTime $datetime = new DateTime()): string
    {
        $dateTimeFormat = DateTime::getFormat();
        $dateTimeFormat = str_replace([":SS", ":ss", ":s"], ["", "", ""], $dateTimeFormat);

        return $datetime->format($dateTimeFormat);
    }

    /**
     * Extending and invoking the parent method
     * 
     * @param mixed $primary
     * @param array $data - array of fields
     */
    public static function update($primary, array $data)
    {
        if (!array_key_exists("TIMESTAMP_X", $data)) {
            $data["TIMESTAMP_X"] = new DateTime();
        }

        if (!array_key_exists("MODIFIED_BY", $data)) {
            $data["MODIFIED_BY"] = CurrentUser::get()->getId();
        }

        parent::update($primary, $data);
    }

    /**
     * Extending and invoking the parent method
     * 
     * @param array $primaries
     * @param array $data - array of fields
     * @param bool  $ignoreEvents
     */
    public static function updateMulti($primaries, $data, $ignoreEvents = false)
    {
        if (!array_key_exists("TIMESTAMP_X", $data)) {
            $data["TIMESTAMP_X"] = new DateTime();
        }

        if (!array_key_exists("MODIFIED_BY", $data)) {
            $data["MODIFIED_BY"] = CurrentUser::get()->getId();
        }

        parent::updateMulti($primaries, $data, $ignoreEvents);
    }
}