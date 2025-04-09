<?php
namespace Brix\Handlers;

use Brix\Api\Controllers\Iblock as ControllerIblock;
use Brix\Config;
use Brix\Helpers\Iblock as HelperIblock;

/**
 * Class Iblock
 * 
 * @package Brix\Handlers
 */
class Iblock
{
    public static function onBeforeIBlockUpdate(&$arFields)
    {
        if (Config::historysave() === "Y") {
            $iblockId = $arFields["ID"];
            $extended = ($arFields["RIGHTS_MODE"] === "E") ? "Y" : "N";
            $arAccess = [];

            if (array_key_exists("RIGHTS", $arFields) && !empty($arFields["RIGHTS"])) {
                foreach ($arFields["RIGHTS"] as $rights) {
                    $arAccess[] = [
                        "code" => $rights["GROUP_CODE"],
                        "option" => $rights["TASK_ID"]
                    ];
                }
            } elseif (array_key_exists("GROUP_ID", $arFields) && !empty($arFields)) {
                $rightsList = HelperIblock::getRightsList();

                foreach ($arFields["GROUP_ID"] as $groupId => $rights) {
                    $taskId = !empty($rights) ? $rightsList["LETTERS"][$rights]["ID"] : "0";
                    $arAccess[] = [
                        "code" => "G" . $groupId,
                        "option" => $taskId
                    ];
                }
            }

            ControllerIblock::saveAccessAction($iblockId, $extended, $arAccess, false);
        }
    }
}