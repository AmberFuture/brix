<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Error, ErrorCollection, Loader, ModuleManager};
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Brix\Helpers\{Conditions, DefaultFields, User, UserFields};
use Brix\Tables\LinkedTaskFieldsTable;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class BrixLinkedDetailComponent extends CBitrixComponent
{
    /**
     * @var string
     */
    const MODULE_ID = "brix.linkedtaskfields";

    /**
     * A collection of errors
     * 
     * @var object
     */
    private ErrorCollection $errors;

    public function executeComponent()
    {
        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_TITLE"));

        $this->errors = new ErrorCollection();

        if (!Loader::IncludeModule(self::MODULE_ID)) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_ERROR_NOT_MODULE")));
        };
        
        if (!ModuleManager::isModuleInstalled("tasks")) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_ERROR_NOT_MODULE_TASKS")));
        };

        if (!User::isAccess()) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_ERROR_ACCESS")));
        }

        $this->prepareParams();
        $this->getField();
        $this->check();

        if (!$this->isErrors()) {
            $this->getUserFields();

            if (!$this->isErrors()) {
                $this->includeComponentTemplate();
            }
        }
    }

    /**
     * Processing incoming parameters
     * 
     * @return void
     **/
    protected function prepareParams(): void
    {
        $this->arResult["FIELD_NAME"] = $this->arParams["FIELD_NAME"] ?? "";
    }

    /**
     * Gets information about the field
     * 
     * @return void
     */
    protected function getField(): void
    {
        $this->arResult["INFO"] = [];

        if (!empty($this->arResult["FIELD_NAME"])) {
            $this->arResult["INFO"] = LinkedTaskFieldsTable::getRow(
                [
                    "select" => [
                        "FIELD_NAME", "FIELD_ID" => "FIELD.ID",
                        "LABEL" => "LANG.EDIT_FORM_LABEL",
                        "MANDATORY" => "FIELD.MANDATORY",
                        "ACTIVE", "REQUIRED", "CONDITIONS"
                    ],
                    "filter" => ["FIELD_NAME" => $this->arResult["FIELD_NAME"]]
                ]
            );
        }
    }

    /**
     * Checking the data
     * 
     * @return void
     */
    protected function check(): void
    {
        global $APPLICATION;
        Toolbar::deleteFavoriteStar();

        if (empty($this->arResult["FIELD_NAME"])) {
            $APPLICATION->SetTitle(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_NEW_RULE"));
        } else {
            if (!$this->arResult["INFO"]) {
                $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_NOT_RULE")));
            } elseif (!$this->arResult["INFO"]["FIELD_ID"]) {
                $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_DELETE_FIELD")));
            } else {
                $fieldName = $this->arResult["INFO"]["LABEL"] ?? $this->arResult["FIELD_NAME"];
                $APPLICATION->SetTitle(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_EDIT_RULE", ["#FIELD#" => $fieldName]));
            }
        }
    }

    /**
     * We get information about all user fields and fields for which rules can be configured.
     * 
     * @return void
     */
    protected function getUserFields(): void
    {
        $this->arResult["LIST_FIELD"] = [];
        $this->arResult["LIST_FIELD_MANDATORY"] = [];
        $this->arResult["CONDITIONS"] = Conditions::get();
        $this->arResult["ALL_FIELDS"] = DefaultFields::get();
        $usFields = UserFields::getListFields();

        if ($usFields) {
            foreach ($usFields as $field) {
                $this->arResult["ALL_FIELDS"][$field["FIELD_NAME"]] = $field;

                if ($field["FIELD_NAME"] === "UF_CRM_TASK") {
                    $this->arResult["ALL_FIELDS"][$field["FIELD_NAME"]]["LABEL"] = "CRM";
                }
            }
        }

        if (empty($this->arResult["FIELD_NAME"])) {
            $db = LinkedTaskFieldsTable::getList([
                "select" => ["FIELD_NAME"]
            ])->fetchAll();
            $arFilter = [];

            if ($db) {
                $arFilter = ["!FIELD_NAME" => array_column($db, "FIELD_NAME")];
            }

            $list = UserFields::getListFields(false, $arFilter);

            if ($list) {
                $this->arResult["LIST_FIELD"] = array_map(function($item) {
                    return ["value" => $item["FIELD_NAME"], "label" => $item["LABEL"]];
                }, $list);
                $this->arResult["LIST_FIELD_MANDATORY"] = array_column($list, "MANDATORY", "FIELD_NAME");
            } else {
                $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_COMPONENT_NOT_FIELD")));
            }
        }
    }

    /**
     * Method for error output and delete favorite star
     * 
     * @return bool
     */
    private function isErrors(): bool
    {
        if (!$this->errors->isEmpty()) {
            Extension::load(["ui.alerts"]);

            foreach ($this->errors as $error) {
                ShowError($error->getMessage(), "ui-alert ui-alert-danger ui-alert-icon-warning ui-alert-inline");
            }

            return true;
        }

        return false;
    }
}