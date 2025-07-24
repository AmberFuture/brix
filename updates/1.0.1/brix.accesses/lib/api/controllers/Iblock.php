<?php
namespace Brix\Api\Controllers;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Engine\{ActionFilter, Controller, CurrentUser};
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Brix\Config;
use Brix\Helpers\Iblock as HelperIblock;
use Brix\Tables\IblockRightsTable;

/**
* Class IblockController
* 
* @package Brix\Api\Controllers
*/
class Iblock extends Controller
{
    public function configureActions()
    {
        return [
            "saveAccess" => [
                "prefilters" => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_GET,
                        ActionFilter\HttpMethod::METHOD_POST
                    ])
                ],
                "postfilters" => []
            ]
        ];
    }

    /**
     * Save access action
     *
     * @param int $iblockId
     * @param string $extended
     * @param array $arAccess
     * @param bool $action
     */
    public static function saveAccessAction(int $iblockId, string $extended, array $arAccess = [], bool $action = true):? array
    {
        if (HelperIblock::isModule()) {
            $userId = CurrentUser::get()->getId();
            $dateTime = new DateTime();
            $rightsMode = ($extended === "Y") ? "E" : "S";
            $oldData = HelperIblock::getAccess([$iblockId]);
            $oldRightsMode = $oldData["IBLOCKS"][$iblockId]["RIGHTS_MODE"];
            $oldAccess = [];
            $historySave = (Config::historysave() === "Y") ? true : false;
            $historyData = [];
            $data = [];

            if ($oldData["IBLOCKS"][$iblockId]["ACCESS"]) {
                foreach ($oldData["IBLOCKS"][$iblockId]["ACCESS"] as $val) {
                    $oldAccess[$val["CODE"]] = [
                        "ID" => $val["ID"],
                        "TASK_ID" => $val["TASK_ID"]
                    ];
                }
            }

            if ($rightsMode !== $oldRightsMode) {
                if ($historySave) {
                    $historyData[] = [
                        "DATE_MODIFIED" => $dateTime,
                        "USER_ID" => $userId,
                        "IBLOCK_ID" => $iblockId,
                        "TYPE" => "change",
                        "DESCRIPTION" => Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_UPDATE_{$rightsMode}")
                    ];
                }

                if ($action) {
                    HelperIblock::deleteAccess($iblockId, $oldRightsMode);
                    IblockTable::update($iblockId, ["RIGHTS_MODE" => $rightsMode]);
                }
            }

            if ($arAccess) {
                $codes = array_column($arAccess, "code");
                $accessName = HelperIblock::accessName($codes);
                $newOptions = array_combine($codes, array_column($arAccess, "option"));
                $oldOptions = [];
                $oldCodeIds = [];
                $arDelete = [];
                $count = 0;

                if ($oldAccess) {
                    $oldCodes = array_keys($oldAccess);
                    $oldOptions = array_combine($oldCodes, array_column($oldAccess, "TASK_ID"));
                    $oldCodeIds = array_combine($oldCodes, array_column($oldAccess, "ID"));

                    foreach ($oldOptions as $code => $opt) {
                        if (!array_key_exists($code, $newOptions)) {
                            if ($rightsMode === "E" && $rightsMode === $oldRightsMode) {
                                $arDelete[] = $oldCodeIds[$code];
                            }

                            if ($historySave) {
                                $id = preg_replace("/[^0-9]/", "", $code);
                                $name = trim($oldData["RIGHTS_NAME"][$code]["provider"] . " " . $oldData["RIGHTS_NAME"][$code]["name"]);

                                if (!empty($id)) {
                                    $name .= " [" . $id . "]";
                                }

                                $old = ((int) $opt === 0) ? Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_DEFAULT_RIGHTS") : $oldData["RIGHTS_LIST"]["EXTENDED"][$opt]["NAME"];
                                $historyData[] = [
                                    "DATE_MODIFIED" => $dateTime,
                                    "USER_ID" => $userId,
                                    "IBLOCK_ID" => $iblockId,
                                    "TYPE" => "delete",
                                    "DESCRIPTION" => Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_DELETE_RIGHTS", ["#NAME#" => $name, "#OLD#" => $old])
                                ];
                            }
                        }
                    }
                }

                foreach ($newOptions as $code => $opt) {
                    $id = preg_replace("/[^0-9]/", "", $code);
                    $type = "";
                    $message = "";
                    $name = array_key_exists($code, $accessName) ? trim($accessName[$code]["provider"] . " " . $accessName[$code]["name"]) : trim($oldData["RIGHTS_NAME"][$code]["provider"] . " " . $oldData["RIGHTS_NAME"][$code]["name"]);

                    if (!empty($id)) {
                        $name .= " [" . $id . "]";
                    }

                    if (array_key_exists($code, $oldOptions)) {
                        if ((int) $opt !== (int) $oldOptions[$code]) {
                            if ($rightsMode === "S") {
                                if (
                                    (int) $opt !== 0 && array_key_exists($opt, $oldData["RIGHTS_LIST"]["NO_EXTENDED"]) &&
                                    ($accessName[$code]["provider_id"] === "group" || $code === "G2")
                                ) {
                                    $type = "change";
                                    $data[$id] = $oldData["RIGHTS_LIST"]["NO_EXTENDED"][$opt]["LETTER"];
                                } else {
                                    $type = "delete";
                                }
                            } else {
                                if ((int) $opt === 0) {
                                    $type = "delete";

                                    if ($rightsMode === $oldRightsMode) {
                                        $arDelete[] = $oldCodeIds[$code];
                                    }
                                } else {
                                    $type = "change";

                                    if ($oldRightsMode === "S") {
                                        $data["n" . $count] = [
                                            "GROUP_CODE" => $code,
                                            "TASK_ID" => $opt
                                        ];
                                        $count++;
                                    } else {
                                        $data[$oldCodeIds[$code]] = [
                                            "GROUP_CODE" => $code,
                                            "TASK_ID" => $opt
                                        ];
                                    }
                                }
                            }
                        } else {
                            if ((int) $opt !== 0) {
                                if ($rightsMode === "E" && $oldRightsMode === "S") {
                                    $data["n" . $count] = [
                                        "GROUP_CODE" => $code,
                                        "TASK_ID" => $opt
                                    ];
                                    $count++;
                                } elseif (
                                    $rightsMode === "S" && array_key_exists($opt, $oldData["RIGHTS_LIST"]["NO_EXTENDED"]) && 
                                    ($accessName[$code]["provider_id"] === "group" || $code === "G2")
                                ) {
                                    $data[$id] = $oldData["RIGHTS_LIST"]["NO_EXTENDED"][$opt]["LETTER"];
                                }
                            }
                        }

                        $old = ((int) $oldOptions[$code] === 0) ? Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_DEFAULT_RIGHTS") : $oldData["RIGHTS_LIST"]["EXTENDED"][$oldOptions[$code]]["NAME"];

                        if ($type === "change") {
                            $message = Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_UPDATE_RIGHTS", ["#NAME#" => $name, "#OLD#" => $old, "#NEW#" => $oldData["RIGHTS_LIST"]["EXTENDED"][$opt]["NAME"]]);
                        } elseif ($type === "delete") {
                            $message = Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_DELETE_RIGHTS", ["#NAME#" => $name, "#OLD#" => $old]);
                        }
                    } else {
                        if (
                            $rightsMode === "E" || 
                            (
                                $rightsMode === "S" && (int) $opt !== 0 && 
                                array_key_exists($opt, $oldData["RIGHTS_LIST"]["NO_EXTENDED"]) && 
                                ($accessName[$code]["provider_id"] === "group" || $code === "G2")
                            )
                        ) {
                            $type = "add";
                            $message = Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_ADD_RIGHTS", ["#NAME#" => $name, "#NEW#" => $oldData["RIGHTS_LIST"]["EXTENDED"][$opt]["NAME"]]);

                            if ($rightsMode === "E") {
                                $data["n" . $count] = [
                                    "GROUP_CODE" => $code,
                                    "TASK_ID" => $opt
                                ];
                                $count++;
                            } else {
                                $data[$id] = $oldData["RIGHTS_LIST"]["NO_EXTENDED"][$opt]["LETTER"];
                            }
                        }
                    }

                    if (!empty($type) && $historySave) {
                        $historyData[] = [
                            "DATE_MODIFIED" => $dateTime,
                            "USER_ID" => $userId,
                            "IBLOCK_ID" => $iblockId,
                            "TYPE" => $type,
                            "DESCRIPTION" => $message
                        ];
                    }
                }

                if ($arDelete && $action) {
                    HelperIblock::deleteIdAccess(IblockRightsTable::class, $arDelete);
                }
            } else {
                if ($oldAccess) {
                    if ($action) {
                        HelperIblock::deleteAccess($iblockId, $oldRightsMode);
                    }

                    if ($historySave) {
                        foreach ($oldAccess as $code => $ac) {
                            $id = preg_replace("/[^0-9]/", "", $code);
                            $name = trim($oldData["RIGHTS_NAME"][$code]["provider"] . " " . $oldData["RIGHTS_NAME"][$code]["name"]);

                            if (!empty($id)) {
                                $name .= " [" . $id . "]";
                            }

                            $old = ((int) $ac["TASK_ID"] === 0) ? Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_DEFAULT_RIGHTS") : $oldData["RIGHTS_LIST"]["EXTENDED"][$ac["TASK_ID"]]["NAME"];

                            $historyData[] = [
                                "DATE_MODIFIED" => $dateTime,
                                "USER_ID" => $userId,
                                "IBLOCK_ID" => $iblockId,
                                "TYPE" => "delete",
                                "DESCRIPTION" => Loc::getMessage("BRIX_ACCESSES_IBLOCK_CONTROLLER_DELETE_RIGHTS", ["#NAME#" => $name, "#OLD#" => $old])
                            ];
                        }
                    }
                }
            }

            if ($data && $action) {
                HelperIblock::saveAccess($iblockId, $data, $rightsMode);
            }

            if ($historyData) {
                HelperIblock::saveHistory($historyData);
            }
        }

        return [];
    }
}