<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Application, Context, Loader, LoaderException, ModuleManager, SystemException};
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Brix\SecretSanta\Config;
use Brix\SecretSanta\Install\{Agents, Db, Dependencies, Files};

Loc::loadMessages(__FILE__);

class brix_secretsanta extends CModule
{
    /** @var string */
    public $MODULE_ID = "brix.secretsanta";
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
        
        $this->MODULE_NAME = Loc::getMessage("BRIX_SECRETSANTA_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("BRIX_SECRETSANTA_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("BRIX_SECRETSANTA_PARTNER_NAME");
        $this->PARTNER_URI = "https://brix.bitrix24site.ru/";
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
            throw new SystemException(Loc::getMessage("BRIX_SECRETSANTA_INSTALL_ERROR_WRONG_VERSION"));
            return false;
        }

        if (!$this->isModuleIntranet()) {
            throw new SystemException(Loc::getMessage("BRIX_SECRETSANTA_INSTALL_ERROR_WRONG_INTRANET"));
            return false;
        }

        if (!$this->isModuleSocialnetwork()) {
            throw new SystemException(Loc::getMessage("BRIX_SECRETSANTA_INSTALL_ERROR_WRONG_SOCIALNETWORK"));
            return false;
        }

        if (!$this->isModuleIm()) {
            throw new SystemException(Loc::getMessage("BRIX_SECRETSANTA_INSTALL_ERROR_WRONG_IM"));
            return false;
        }

        try {
            ModuleManager::registerModule($this->MODULE_ID);
            Loader::includeModule($this->MODULE_ID);
            Db::install();
            Files::install();
            Dependencies::install();

            if (!Option::getForModule($this->MODULE_ID)) {
                Config::setDefaultOptions();
            }
        } catch (LoaderException $e) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            throw new SystemException(Loc::getMessage("BRIX_SECRETSANTA_INSTALL_ERROR"));
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
            $APPLICATION->IncludeAdminFile(Loc::getMessage("BRIX_SECRETSANTA_UNINSTALL_TITLE"), $this->getPath() . "/install/unstep1.php");
        } elseif ($step == 2) {
            $request = Context::getCurrent()->getRequest();
            
            if (Loader::includeModule("im")) {
                \CIMNotify::DeleteBySubTag($this->MODULE_ID . "|invite");
            }

            Agents::uninstall();

            if ($request["save_options"] !== "Y") {
                Option::delete($this->MODULE_ID);
            } else {
                Config::dateregistration();
                Config::datestart();
                Config::datecompletion();
                Config::noticedate();
            }

            if ($request["save_tables"] !== "Y") {
                Db::uninstall();
            }
            
            Dependencies::uninstall();
            Files::uninstall();
            ModuleManager::unregisterModule($this->MODULE_ID);
            
            $APPLICATION->IncludeAdminFile(Loc::getMessage("BRIX_SECRETSANTA_UNINSTALL_TITLE"), $this->getPath() . "/install/unstep2.php");
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
     * Check Intranet installed
     * 
     * @return bool
     */
    protected function isModuleIntranet(): bool
    {
        return ModuleManager::isModuleInstalled("intranet");
    }

    /**
     * Check Socialnetwork installed
     * 
     * @return bool
     */
    protected function isModuleSocialnetwork(): bool
    {
        return ModuleManager::isModuleInstalled("socialnetwork");
    }

    /**
     * Check Im installed
     * 
     * @return bool
     */
    protected function isModuleIm(): bool
    {
        return ModuleManager::isModuleInstalled("im");
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