<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Context, Loader, Error, ErrorCollection};
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Brix\Helpers\{Access, Iblock};

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class BrixAccessesSettingsComponent extends CBitrixComponent
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
    
    /**
     * @var array
     */
    private array $supportedTagSelectorList = [
        "IBLOCK",
        "IBLOCK_MANY"
    ];
    private array $containerTagSelectorList = [
        "iblock_selector",
        "iblock_many_selector"
    ];
    private array $defaultTagSelectorSettings = [
        "id" => "brix_settings",
        "multiple" => false,
        "dialogOptions" => [
            "id" => "brix_settings",
            "entities" => [
                [
                    "id" => "brix_iblocks"
                ]
            ]
        ]
    ];

    public function executeComponent()
    {
        global $APPLICATION;
        $this->errors = new ErrorCollection();
        $APPLICATION->SetTitle(Loc::getMessage("BRIX_ACCESSES_SETTINGS_COMPONENT_TITLE"));

        if (!Loader::IncludeModule(self::MODULE_ID)) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_SETTINGS_COMPONENT_ERROR_NOT_MODULE")));
        };
        
        if (!Loader::IncludeModule("iblock")) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_SETTINGS_COMPONENT_ERROR_NOT_MODULE_IBLOCK")));
        };

        if (!$this->isAccess()) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_SETTINGS_COMPONENT_ERROR_ACCESS")));
        }

        if (!$this->isErrors()) {
            $bodyClass = $APPLICATION->GetPageProperty("BodyClass");
            $APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass . " " : "") . "no-all-paddings");

            $this->prepareParams();
            $this->preparePost();
            $this->prepareSteps();
            $this->includeComponentTemplate();
        }
    }

    /**
     * Checking access to the page
     * 
     * @return bool
     */
    private function isAccess()
    {
        $this->arResult["CHANGE"] = Access::getAccess("change");
        $this->arResult["VIEW"] = Access::getAccess();

        return ($this->arResult["VIEW"] || $this->arResult["CHANGE"]);
    }

    /**
     * Processing incoming parameters
     * 
     * @return void
     **/
    protected function prepareParams(): void
    {
        $this->arResult["SEF_FOLDER"] = $this->arParams["SEF_FOLDER"];
    }

    /**
     * Processing POST parameters
     * 
     * @return void
     */
    protected function preparePost(): void
    {
        $request = Context::getCurrent()->getRequest();
        $this->arResult["STEP"] = $request["STEP"] ?? (!$this->arResult["CHANGE"] ? 2 : 1);
        $this->arResult["COMMON"] = !$this->arResult["CHANGE"] ? "N" : (($request["COMMON"] && $request["COMMON"] === "Y") ? $request["COMMON"] : "N");
        $this->arResult["IBLOCK"] = $request["IBLOCK"] ?? "";
        $this->arResult["IBLOCK_MANY"] = $request["IBLOCK_MANY"] ?? "";
    }

    /**
     * Additional processing steps
     * 
     * @return void
     */
    protected function prepareSteps(): void
    {
        $this->arResult["TAGSELECTOR"] = [];
        $this->arResult["ACCESS_INFO"] = [];
        $this->arResult["IBLOCK_MANY_INFO"] = [];

        if ((int) $this->arResult["STEP"] === 2) {
            foreach ($this->supportedTagSelectorList as $key => $tagSelectorId) {
                $currentTagSelectorData = $this->defaultTagSelectorSettings;
                $currentTagSelectorData["id"] .= "_{$tagSelectorId}";
                $currentTagSelectorData["dialogOptions"]["id"] .= "_{$tagSelectorId}";

                if ($tagSelectorId === "IBLOCK_MANY") {
                    $currentTagSelectorData["multiple"] = true;
                    $arSelected = $this->arResult["IBLOCK_MANY"] ? explode(",", $this->arResult["IBLOCK_MANY"]) : [];
                    if ($arSelected) {
                        foreach ($arSelected as $elem) {
                            $currentTagSelectorData["dialogOptions"]["preselectedItems"][] = ["brix_iblocks", $elem];
                        }
                    }
                } else {
                    $currentTagSelectorData["dialogOptions"]["preselectedItems"] = $this->arResult["IBLOCK"] ? [["brix_iblocks" , $this->arResult["IBLOCK"]]] : [];
                }

                $currentTagSelectorData["placeholder"] = Loc::getMessage("BRIX_ACCESSES_SETTINGS_COMPONENT_PLACEHOLDER_{$tagSelectorId}");

                $this->arResult["TAGSELECTOR"][$tagSelectorId] = [
                    "container" => $this->containerTagSelectorList[$key],
                    "settings" => $currentTagSelectorData
                ];
            }
        } elseif ((int) $this->arResult["STEP"] === 3) {
            $iblocks = !empty($this->arResult["IBLOCK"]) ? [$this->arResult["IBLOCK"]] : explode(",", $this->arResult["IBLOCK_MANY"]);
            $iblocks = array_map("intval", $iblocks);
            $this->arResult["ACCESS_INFO"] = Iblock::getAccess($iblocks);

            if (!empty($this->arResult["IBLOCK"])) {
                $arIblockMany = array_map("intval", explode(",", $this->arResult["IBLOCK_MANY"]));
                $this->arResult["IBLOCK_MANY_INFO"] = Iblock::getIblock($arIblockMany);
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
            Toolbar::deleteFavoriteStar();

            foreach ($this->errors as $error) {
                ShowError($error->getMessage(), "ui-alert ui-alert-danger ui-alert-icon-warning ui-alert-inline");
            }

            return true;
        }

        return false;
    }
}