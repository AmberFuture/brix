<?php
namespace Brix\Tables;

use Bitrix\Iblock\{ElementTable, IblockTable, SectionTable};
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\{IntegerField, StringField, Validators};
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\TaskTable;

/**
* Class IblockRightsTable
*
* @package Brix\Tables
*/
class IblockRightsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     * 
     * @return string
     */
    public static function getTableName()
    {
        return "b_iblock_right";
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
                "autocomplete" => true
            ]),
            new IntegerField("IBLOCK_ID", [
                "required" => true
            ]),
            new StringField("GROUP_CODE", [
                "required" => true,
                "validation" => function()
                {
                    return [
                        new Validators\LengthValidator(null, 50)
                    ];
                }
            ]),
            new StringField("ENTITY_TYPE", [
                "required" => true,
                "validation" => function()
                {
                    return [
                        new Validators\LengthValidator(null, 32),
                    ];
                }
            ]),
            new IntegerField("ENTITY_ID", [
                "required" => true
            ]),
            new StringField("DO_INHERIT", [
                "required" => true,
                "default" => "Y",
                "validation" => function()
                {
                    return [
                        new Validators\LengthValidator(null, 1),
                    ];
                }
            ]),
            new IntegerField("TASK_ID", [
                "required" => true
            ]),
            new StringField("OP_SREAD", [
                "required" => true,
                "default" => "Y",
                "validation" => function()
                {
                    return [
                        new Validators\LengthValidator(null, 1),
                    ];
                }
            ]),
            new StringField("OP_EREAD", [
                "required" => true,
                "default" => "Y",
                "validation" => function()
                {
                    return [
                        new Validators\LengthValidator(null, 1),
                    ];
                }
            ]),
            new StringField("XML_ID", [
                "validation" => function()
                {
                    return [
                        new Validators\LengthValidator(null, 32),
                    ];
                }
            ]),
            new Reference(
                "IBLOCK",
                IblockTable::class,
                Join::on("this.IBLOCK_ID", "ref.ID")
            ),
            new Reference(
                "ELEMENT",
                ElementTable::class,
                Join::on("this.ENTITY_ID", "ref.ID")
            ),
            new Reference(
                "SECTION",
                SectionTable::class,
                Join::on("this.ENTITY_ID", "ref.ID")
            ),
            new Reference(
                "TASK",
                TaskTable::class,
                Join::on("this.TASK_ID", "ref.ID")
            ),
        ];
    }
}