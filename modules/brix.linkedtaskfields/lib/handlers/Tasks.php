<?php
namespace Brix\Handlers;

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Tasks\Control\Exception\{TaskAddException, TaskUpdateException};
use Brix\Api\Controllers\Task;
use Brix\Helpers\UserFields;

/**
 * Class Tasks
 * 
 * @package Brix\Handlers
 */
class Tasks
{
    /**
     * Method for the issue creation event
     * 
     * @param array $arFields
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws TaskAddException
     */
    public static function onBeforeTaskAdd(&$arFields): bool
    {
        $error = self::checkTask(0, $arFields);

        if (!empty($error)) {
            throw new TaskAddException($error);
            return false;
        }

        return true;
    }

    /**
     * Method for the issue update event
     * 
     * @param int $id
     * @param array $arFields
     * @param array $arFieldsPrev
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws TaskUpdateException
     */
    public static function onBeforeTaskUpdate($id, &$arFields, &$arFieldsPrev): bool
    {
        $error = self::checkTask((int) $id, $arFields);

        if (!empty($error)) {
            throw new TaskUpdateException($error);
            return false;
        }

        return true;
    }

    /**
     * Checks whether the required fields are filled in according to the conditions
     * 
     * @param int $taskId
     * @param array $arFields
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    protected static function checkTask(int $taskId = 0, array $arFields = []): string
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();
        $arGet = $request->getQueryList()->toArray();
        $server = $context->getServer();
        $referer = $server->get("HTTP_REFERER");
        $errorText = "";

        if (
            (array_key_exists("action", $arGet) && str_starts_with($arGet["action"], "tasksmobile")) ||
            (!str_contains($referer, "/view/") && !str_contains($referer, "/edit/"))
        ) {
            return $errorText;
        }

        $task = Task::getAction($taskId, $arFields);
        $arData = [];

        if ($task["CONDITIONS"]) {
            foreach ($task["CONDITIONS"] as $field => $data) {
                if (
                    $data["REQUIRED"] === "Y" && !in_array($field, $task["HIDE_FIELD"]) &&
                    (!array_key_exists($field, $arFields) || !$arFields[$field])
                ) {
                    $arData[] = $field;
                }
            }
        }

        if ($arData) {
            $userFields = UserFields::getListFields(false, ["FIELD_NAME" => $arData]);

            if ($userFields) {
                $arNames = array_column($userFields, "LABEL");
                $strNames = implode(", ", $arNames);

                echo '<script>
                window.brixTaskParams = ' . Json::encode($arFields) . '
                </script>';

                $errorText = Loc::getMessage("BRIX_LINKEDTASKFIELDS_HANDLERS_TASKS_ERROR", ["#FIELDS#" => $strNames]);
            }
        }

        return $errorText;
    }
}