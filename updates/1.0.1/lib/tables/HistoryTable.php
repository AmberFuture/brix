<?php
namespace Brix\Tables;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\{DatetimeField, EnumField, IntegerField, StringField, Validators};
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

/**
* Class HistoryTable
*
* @package Brix\Tables
*/
class HistoryTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     * 
     * @return string
     */
    public static function getTableName()
    {
        return "brix_accesses_history";
    }
    
    /**
     * Returns entity map definition.
     * 
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField("ID", [
                "primary" => true,
                "autocomplete" => true,
                "title" => Loc::getMessage("BRIX_ACCESSES_HISTORY_TABLE_NAME_ID")
            ]),
            new DatetimeField("DATE_MODIFIED", [
                "nullable" => true,
                "default" => new DateTime(),
                "title" => Loc::getMessage("BRIX_ACCESSES_HISTORY_TABLE_NAME_DATE_MODIFIED")
            ]),
            new IntegerField("USER_ID", [
                "required" => true,
                "default" => CurrentUser::get()->getId(),
                "title" => Loc::getMessage("BRIX_ACCESSES_HISTORY_TABLE_NAME_USER_ID")
            ]),
            new IntegerField("IBLOCK_ID", [
                "required" => true,
                "title" => Loc::getMessage("BRIX_ACCESSES_HISTORY_TABLE_NAME_IBLOCK_ID")
            ]),
            new EnumField("TYPE", [
                "required" => true,
                "values" => ["add", "change", "delete"],
                "title" => Loc::getMessage("BRIX_ACCESSES_HISTORY_TABLE_NAME_TYPE")
            ]),
            new StringField("DESCRIPTION", [
                "required" => true,
                "validation" => function()
                {
                    return [
                        new Validators\LengthValidator(null, 275),
                    ];
                },
                "title" => Loc::getMessage("BRIX_ACCESSES_HISTORY_TABLE_NAME_DESCRIPTION")
            ]),
            new Reference(
                "USER",
                UserTable::class,
                Join::on("this.USER_ID", "ref.ID")
            ),
            new Reference(
                "IBLOCK",
                IblockTable::class,
                Join::on("this.IBLOCK_ID", "ref.ID")
            ),
        ];
    }
}