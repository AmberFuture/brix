<?php
namespace Brix\Handlers;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\{Loader, ModuleManager};
use Bitrix\Main\UI\Extension;

/**
 * Class Main
 * 
 * @package Brix\Handlers
 */
class Main
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.linkedtaskfields";

    /**
     * Method for the OnProlog event
     * 
     * @return void
     */
    public static function onProlog(): void
    {
        if (Loader::includeModule(self::MODULE_ID) && ModuleManager::isModuleInstalled("tasks")) {
            $engine = new \CComponentEngine();
            $pageUser = $engine->guessComponentPath(
                "/company/personal/user/",
                [
                    "default" => "#user_id#/tasks/",
                    "tasks" => "#user_id#/tasks/task/#action#/#task_id#/"
                ],
                $variables,
                false
            );
            $pageGroup = $engine->guessComponentPath(
                "/workgroups/group/",
                [
                    "default" => "#group_id#/tasks/",
                    "tasks" => "#group_id#/tasks/task/#action#/#task_id#/"
                ],
                $variablesGroup,
                false
            );

            if (!empty($pageUser) || !empty($pageGroup)) {
                if (CurrentUser::get()->isAdmin() && ($pageUser === "default" || $pageGroup === "default")) {
                    Extension::load(["brix_linked_task_settings", "brix_linked_modal_css"]);
                } elseif (
                    ($pageUser === "tasks" && $variables["action"] === "edit") ||
                    ($pageGroup === "tasks" && $variablesGroup["action"] === "edit")
                ) {
                    Extension::load(["brix_linked_task_edit"]);
                }
            }
        }
    }
}