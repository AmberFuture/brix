<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Application, Context, Loader, LoaderException, ModuleManager, SystemException};
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Brix\Config;
use Brix\Install\{Agents, Db, Dependencies, Files, LeftMenu, UrlRewrite};

Loc::loadMessages(__FILE__);

class brix_accesses extends CModule
{
    /** @var string */
    public $MODULE_ID = "brix.accesses";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "Y";
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
    
    /**
     * Class Constructor
     * 
     * @return void;
     */
    public function __construct()
    {
        $arModuleVersion = [];
        
        include __DIR__ . "/version.php";
        
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        
        $this->MODULE_NAME = Loc::getMessage("BRIX_ACCESSES_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("BRIX_ACCESSES_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("BRIX_ACCESSES_PARTNER_NAME");
        $this->PARTNER_URI = "https://marketplace.1c-bitrix.ru/partners/detail.php?ID=23775424.php";
    }

    /**
     * Installing everything you need when installing the module
     * 
     * @throws \Bitrix\Main\SystemException
     * @throws \Bitrix\Main\LoaderException
     */
    public function DoInstall()
    {
        if (!$this->isVersionD7()) {
            throw new SystemException(Loc::getMessage("BRIX_ACCESSES_INSTALL_ERROR_WRONG_VERSION"));
            return false;
        }

        if (!$this->isModuleIblock()) {
            throw new SystemException(Loc::getMessage("BRIX_ACCESSES_INSTALL_ERROR_WRONG_IBLOCK"));
            return false;
        }

        try {
            ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);
            Db::install();
            Files::install();
            UrlRewrite::install();
            Dependencies::install();
            Agents::install();
            LeftMenu::install();

            if (!Option::getForModule($this->MODULE_ID)) {
                Config::setDefaultOptions();
            }
        } catch (LoaderException $e) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            throw new SystemException(Loc::getMessage("BRIX_ACCESSES_INSTALL_ERROR"));
        }
    }

    /**
     * Removing everything needed when uninstalling a module
     * 
     * @param array $arParams
     */
    public function DoUninstall($arParams = [])
    {
        Loader::includeModule($this->MODULE_ID);
        global $APPLICATION, $step;
        
        $step = intval($step);

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("BRIX_ACCESSES_UNINSTALL_TITLE"), $this->getPath() . "/install/unstep1.php");
        } elseif ($step == 2) {
            $request = Context::getCurrent()->getRequest();

            if ($request["save_options"] !== "Y") {
                Option::delete($this->MODULE_ID);
            }

            if ($request["save_tables"] !== "Y") {
                Db::uninstall();
            }

            LeftMenu::uninstall();
            UrlRewrite::uninstall();
            Agents::uninstall();
            Dependencies::uninstall();
            Files::uninstall();
            ModuleManager::unregisterModule($this->MODULE_ID);
            
            $APPLICATION->IncludeAdminFile(Loc::getMessage("BRIX_ACCESSES_UNINSTALL_TITLE"), $this->getPath() . "/install/unstep2.php");
        }
    }

    /**
     * Check D7 version
     * 
     * @return bool
     */
    protected function isVersionD7(): bool
    {
        return (version_compare(ModuleManager::getVersion("main"), "14.00.00") >= 0);
    }

    /**
     * Check Iblock installed
     * 
     * @return bool
     */
    protected function isModuleIblock(): bool
    {
        return ModuleManager::isModuleInstalled("iblock");
    }

    /**
     * The function returns the current PATH for the installer
     * 
     * @param bool $notDocumentRoot
     * 
     * @return mixed|string
     */
    protected function getPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot) {
            return str_ireplace(Application::getDocumentRoot(), "", dirname(__DIR__));
        } else {
            return dirname(__DIR__);
        }
    }
}