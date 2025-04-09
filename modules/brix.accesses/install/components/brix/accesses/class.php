<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Context, Loader, Error, ErrorCollection};
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class BrixAccessesComponent extends CBitrixComponent
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
        $this->errors = new ErrorCollection();
        
        if (!Loader::IncludeModule(self::MODULE_ID)) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_COMPONENT_ERROR_NOT_MODULE")));
        };
        
        if (!Loader::IncludeModule("iblock")) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_COMPONENT_ERROR_NOT_MODULE_IBLOCK")));
        };

        if (!$this->isErrors()) {
            $startPage = $this->arParams["SEF_FOLDER"] ?? explode("/", Context::getCurrent()->getRequest()->getRequestedPageDirectory())[1];
            $componentPage = "settings";
            $arDefaultUrlTemplates404 = [
                "settings" => "",
                "history" => "history/",
                "detail" => "history/#ID#/",
            ];
            $arDefaultVariableAliases404 = [];
            $arComponentVariables = ["ID"];
            $arVariables = [];
            $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $this->arParams["SEF_URL_TEMPLATES"]);
            $arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $this->arParams["VARIABLE_ALIASES"] ?? []);
            $componentPage = CComponentEngine::ParseComponentPath($startPage, $arUrlTemplates, $arVariables);
            
            if (!(is_string($componentPage) && isset($componentPage[0]) && isset($arDefaultUrlTemplates404[$componentPage]))) {
                $componentPage = "settings";
            }
            
            CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
            
            $this->arResult = [
                "SEF_FOLDER" => $startPage,
                "URL_TEMPLATES" => $arUrlTemplates,
                "VARIABLES" => $arVariables,
                "ALIASES" => $arVariableAliases,
            ];
            
            $this->includeComponentTemplate($componentPage);
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
            Toolbar::deleteFavoriteStar();

            foreach ($this->errors as $error) {
                ShowError($error->getMessage(), "ui-alert ui-alert-danger ui-alert-icon-warning ui-alert-inline");
            }

            return true;
        }

        return false;
    }
}