<?php
namespace Brix\Helpers;

use CAccess;
use CIBlock;
use CIBlockRights;
use Bitrix\Iblock\{IblockGroupTable, IblockTable};
use Bitrix\Main\{Application, Loader, SystemException, TaskTable};
use Bitrix\Main\Localization\Loc;
use Brix\Helpers\UserGroup;
use Brix\Tables\{HistoryTable, IblockRightsTable};

/**
 * Class Iblock
 * 
 * @package Brix\Helpers
 */
class Iblock
{
    /**
     * @var string
     */
    public const MODULE_ID = "brix.accesses";

    /**
     * Checks if the information blocks module is installed.
     * 
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function isModule(): bool
    {
        if (!Loader::IncludeModule(self::MODULE_ID)) {
            throw new SystemException(Loc::getMessage("BRIX_ACCESSES_IBLOCK_ERROR_NOT_MODULE_BRIX"));
            return false;
        } elseif (!Loader::IncludeModule("iblock")) {
            throw new SystemException(Loc::getMessage("BRIX_ACCESSES_IBLOCK_ERROR_NOT_MODULE_IBLOCK"));
            return false;
        }

        return true;
    }

    /**
     * Get access to the information block
     *
     * @param array $iblocks
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     **/
    public static function getAccess(array $iblocks = []): array
    {
        if (!self::isModule()) {
            return [];
        }

        $arResult = [
            "IBLOCKS" => [],
            "RIGHTS_NAME" => [],
        ];
        $rightsList = self::getRightsList();
        $letters = $rightsList["LETTERS"];
        $arResult["RIGHTS_LIST"]["EXTENDED"] = $rightsList["TASKS"];
        $arResult["RIGHTS_LIST"]["NO_EXTENDED"] = $rightsList["TASKS"];
        unset($arResult["RIGHTS_LIST"]["NO_EXTENDED"][$letters["E"]["ID"]]);
        unset($arResult["RIGHTS_LIST"]["NO_EXTENDED"][$letters["U"]["ID"]]);
        $arIblocks = self::getIblock($iblocks);
        $ibExtended = $arIblocks["EXTENDED"];
        $ibNoExtended = $arIblocks["NO_EXTENDED"];
        $dTask = $letters["D"]["ID"];
        $groupCodes = [];

        if ($ibExtended) {
            $rights = IblockRightsTable::getList([
                "filter" => ["IBLOCK_ID" => $ibExtended, "ENTITY_TYPE" => "iblock"],
                "select" => ["ID", "IBLOCK_ID", "GROUP_CODE", "TASK_ID"],
                "group" => ["IBLOCK_ID"]
            ])->fetchAll();

            if ($rights) {
                foreach ($rights as $right) {
                    $arIblocks["IBLOCKS"][$right["IBLOCK_ID"]]["ACCESS"][] = [
                        "ID" => $right["ID"],
                        "CODE" => $right["GROUP_CODE"],
                        "TASK_ID" => $right["TASK_ID"]
                    ];
                    $groupCodes[] = $right["GROUP_CODE"];
                }
            }
        }

        if ($ibNoExtended) {
            $dbGroups = UserGroup::getGroupList(true, false, ["C_SORT" => "ASC", "ID" => "DESC"]);
            $ibGroups = IblockGroupTable::getList(["filter" => ["IBLOCK_ID" => $ibNoExtended], "select" => ["IBLOCK_ID", "GROUP_ID", "PERMISSION"]])->fetchAll();

            if ($dbGroups) {
                $groups = array_keys($dbGroups);
                $keys = array_keys($groups);
                $groupCodes = array_merge($groupCodes, array_map(function($id)
                    {
                        return "G" . $id;
                    },
                    $groups)
                );
                $arAccess = array_map(function($id) use ($dTask)
                {
                    $taskId = "0";

                    if ((int) $id === 2) {
                        $taskId = $dTask;
                    }

                    return [
                        "ID" => $id,
                        "CODE" => "G" . $id,
                        "TASK_ID" => $taskId
                    ];
                },
                $groups);
                $access = array_combine($keys, $arAccess);
                $rights = [];

                foreach ($ibNoExtended as $id) {
                    $rights[$id] = $access;
                }

                if ($ibGroups) {
                    foreach ($ibGroups as $group) {
                        $key = array_search($group["GROUP_ID"], $groups);
                        $rights[$group["IBLOCK_ID"]][$key]["TASK_ID"] = $letters[$group["PERMISSION"]]["ID"];
                    }
                }

                foreach ($rights as $iblockId => $right) {
                    $arIblocks["IBLOCKS"][$iblockId]["ACCESS"] = $right;
                }
            }
        }

        $arResult["IBLOCKS"] = $arIblocks["IBLOCKS"];

        if ($groupCodes) {
            $arResult["RIGHTS_NAME"] = self::accessName($groupCodes);
        }

        return $arResult;
    }

    /**
     * Gets basic information about the information block
     * 
     * @param array $iblocks
     * @return array
     * [
     *      ID => [
     *          "ID" => ID,
     *          "NAME" => NAME,
     *          "RIGHTS_MODE" => S/E,
     *          "EXTENDED" => Y/N
     *      ]
     * ]
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getIblock(array $iblocks = []): array
    {
        $arResult = [
            "IBLOCKS" => [],
            "EXTENDED" => [],
            "NO_EXTENDED" => []
        ];

        if (self::isModule()) {
            $filter = $iblocks ? ["ID" => array_unique($iblocks)] : [];
            $dbList = IblockTable::getList([
                "select" => ["ID", "NAME", "RIGHTS_MODE"],
                "filter" => $filter
            ])->fetchAll();

            if ($dbList) {
                foreach ($dbList as $ib) {
                    $arResult["IBLOCKS"][$ib["ID"]] = [
                        "ID" => $ib["ID"],
                        "NAME" => $ib["NAME"],
                        "RIGHTS_MODE" => $ib["RIGHTS_MODE"],
                        "EXTENDED" => ($ib["RIGHTS_MODE"] === "E") ? "Y" : "N",
                    ];

                    if ($ib["RIGHTS_MODE"] === "E") {
                        $arResult["EXTENDED"][] = $ib["ID"];
                    } else {
                        $arResult["NO_EXTENDED"][] = $ib["ID"];
                    }
                }
            }
        }

        return $arResult;
    }

    /**
     * List of access levels
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     **/
    public static function getRightsList(): array
    {
        $result = [];
        $docRoot = Application::getDocumentRoot();
        $localize = [];
        
        if (($path = getLocalPath("modules/iblock/admin/task_description.php")) !== false) {
            $localize = include($docRoot . $path);
        }
        
        $tasks = TaskTable::getList([
            "filter" => ["MODULE_ID" => "iblock"],
            "select" => ["ID", "LETTER", "NAME"],
        ])->fetchAll();
        
        foreach ($tasks as $task) {
            $result["LETTERS"][$task["LETTER"]] = [
                "ID" => $task["ID"],
                "NAME" => array_key_exists(strtoupper($task["NAME"]), $localize) ? $localize[strtoupper($task["NAME"])]["title"] : $task["NAME"],
            ];
            $result["TASKS"][$task["ID"]] = [
                "LETTER" => $task["LETTER"],
                "NAME" => array_key_exists(strtoupper($task["NAME"]), $localize) ? $localize[strtoupper($task["NAME"])]["title"] : $task["NAME"],
            ];
        }

        return $result;
    }

    /**
     * Information on access group codes
     *
     * @param array $groupCodes | Array of access group codes
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     **/
    public static function accessName(array $groupCodes): array
    {
        $access = new CAccess();
        $result = $access->GetNames(array_unique($groupCodes));

        return $result;
    }

    /**
     * Deletes access rights
     * 
     * @param class $class
     * @param array $ids
     * @return void
     * @throws ArgumentException
     */
    public static function deleteIdAccess($class, array $ids): void
    {
        foreach ($ids as $id) {
            $class::delete($id);
        }
    }

    /**
     * Deletes access rights
     * 
     * @param int $iblockId
     * @param string $newExt
     * @return void
     * @throws ArgumentException
     */
    public static function deleteAccess(int $iblockId, string $newExt): void
    {
        if ($newExt === "S") {
            CIBlock::SetPermission($iblockId, []);
        } elseif ($newExt === "E") {
            $rights = new CIBlockRights($iblockId);
            $rights->DeleteAllRights();
        }
    }

    /**
     * Set access for block
     *
     * @param int $iblockId
     * @param array $data
     * ["1"=>"X", "2"=>"R", "3"=>"W"]
     * OR
     * [
     *  id => [
     *      GROUP_CODE => G1,
     *      TASK_ID => 62,
     *      ... (all columns)
     *  ],
     *  n0|n1... => [
     *      GROUP_CODE => G1,
     *      TASK_ID => 62
     *  ]
     * ]
     * @param string $ext
     * @return void
     * @throws ArgumentException
     **/
    public static function saveAccess(int $iblockId, array $data, string $ext): void
    {
        if (self::isModule()) {
            if ($ext === "S") {
                CIBlock::SetPermission($iblockId, $data);
            } elseif ($ext === "E") {
                $adds = [];

                foreach ($data as $k => $v) {
                    $v["IBLOCK_ID"] = $iblockId;
                    $v["ENTITY_ID"] = $iblockId;
                    $v["ENTITY_TYPE"] = "iblock";
                    $v["DO_INHERIT"] = "Y";
                    $v["OP_SREAD"] = "Y";
                    $v["OP_EREAD"] = "Y";
                    $firstChar = substr($k, 0, 1);

                    if ($firstChar === "n") {
                        $adds[] = $v;
                    } else {
                        IblockRightsTable::update($k, $v);
                    }
                }

                if ($adds) {
                    IblockRightsTable::addMulti($adds);
                }
            }
        }
    }

    /**
     * Adds an entry to the access change history
     * 
     * @param array $data
     * [
     *  "DATE_MODIFIED" => new DateTime(),
     *  "USER_ID" => 1,
     *  "IBLOCK_ID" => 1,
     *  "TYPE" => "add|change|delete",
     *  "DESCRIPTION" => "text"
     * ]
     * @return void
     * @throws ArgumentException
     */
    public static function saveHistory(array $data): void
    {
        HistoryTable::addMulti($data);
    }
}