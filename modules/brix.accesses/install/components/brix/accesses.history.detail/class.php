<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Error, ErrorCollection, Loader};
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Brix\Helpers\{Access, User};
use Brix\Tables\HistoryTable;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class BrixAccessesHistoryDetailComponent extends CBitrixComponent
{
    /**
     * @var string
     */
    const MODULE_ID = "brix.accesses";

    /**
     * A collection of errors
     * 
     * @var object
     */
    private ErrorCollection $errors;

    public function executeComponent()
    {
        global $APPLICATION;
        $this->errors = new ErrorCollection();
        Toolbar::deleteFavoriteStar();

        if (!Loader::IncludeModule(self::MODULE_ID)) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_ERROR_NOT_MODULE")));
        };
        
        if (!Loader::IncludeModule("iblock")) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_ERROR_NOT_MODULE_IBLOCK")));
        };

        if (!$this->isAccess()) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_ERROR_ACCESS")));
        }

        if ((int) $this->arParams["ID"] <= 0) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_NOT_ID")));
        }

        if (!$this->isErrors()) {
            $this->prepareParams();
            $this->getRow();

            if (!$this->isErrors()) {
                $APPLICATION->SetTitle(Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_TITLE", ["#ID#" => $this->arResult["ID"]]));
                $this->includeComponentTemplate();
            }
        }
    }

    /**
     * Checking access to the page
     * 
     * @return bool
     */
    private function isAccess()
    {
        $this->arResult["VIEW"] = Access::getAccess("history");

        return $this->arResult["VIEW"];
    }

    /**
     * Processing incoming parameters
     * 
     * @return void
     **/
    protected function prepareParams(): void
    {
        $this->arResult["ID"] = (int) $this->arParams["ID"];
    }

    /**
     * Getting information about a record
     * 
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @return void
     **/
    protected function getRow()
    {
        $this->arResult["ROW"] = HistoryTable::getRow([
            "select" => ["*", "USER_PHOTO" => "USER.PERSONAL_PHOTO", "IBLOCK_NAME" => "IBLOCK.NAME"],
            "filter" => ["ID" => $this->arResult["ID"]],
            "cache" => [
                "ttl" => 36000,
                "cache_joins" => true
            ]
        ]);

        if ($this->arResult["ROW"]) {
            $dateTimeFormat = DateTime::getFormat();
            $dateTimeFormat = str_replace([":SS", ":ss", ":s"], ["", "", ""], $dateTimeFormat);
            $userId = $this->arResult["ROW"]["USER_ID"];
            $users = User::getUserFormated([$userId]);
            $this->arResult["ROW"]["USER_NAME"] = "";
            $this->arResult["ROW"]["USER_PHOTO_PATH"] = "";

            if (array_key_exists($userId, $users)) {
                $this->arResult["ROW"]["USER_NAME"] = !empty($users[$userId]) ? $users[$userId] : Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_USER_ID_NO_NAME", ["#ID#" => $userId]);
            }

            if (!empty($this->arResult["ROW"]["USER_PHOTO"])) {
                $this->arResult["ROW"]["USER_PHOTO_PATH"] = str_starts_with($this->arResult["ROW"]["USER_PHOTO"], "http") ? $this->arResult["ROW"]["USER_PHOTO"] : \CFile::GetPath($this->arResult["ROW"]["USER_PHOTO"]);
            }

            $ibname = $this->arResult["ROW"]["IBLOCK_NAME"];
            unset($this->arResult["ROW"]["IBLOCK_NAME"]);
            $this->arResult["ROW"]["IBLOCK_NAME"] = $ibname ? $ibname . " [" . $this->arResult["ROW"]["IBLOCK_ID"] . "]" : Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_IBLOCK_ID_DELETE", ["#ID#" => $this->arResult["ROW"]["IBLOCK_ID"]]);

            $date = $this->arResult["ROW"]["DATE_MODIFIED"];
            unset($this->arResult["ROW"]["DATE_MODIFIED"]);
            $this->arResult["ROW"]["DATE_MODIFIED"] = "";

            if ($date) {
                $this->arResult["ROW"]["DATE_MODIFIED"] = $date->format($dateTimeFormat);
            }

            $type = $this->arResult["ROW"]["TYPE"];
            unset($this->arResult["ROW"]["TYPE"]);
            $this->arResult["ROW"]["TYPE"] = Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_TYPE")[$type];
        } else {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_DETAIL_COMPONENT_NOT_FOUND_ID", ["#ID#" => $this->arResult["ID"]])));
        }
    }
    
    /**
     * Method for error output
     * 
     * @return bool
     */
    private function isErrors(): bool
    {
        if (!$this->errors->isEmpty()) {
            foreach ($this->errors as $error) {
                ShowError($error->getMessage(), "ui-alert ui-alert-danger ui-alert-icon-warning ui-alert-inline");
            }

            return true;
        }

        return false;
    }
}