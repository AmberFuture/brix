<?php
namespace Brix\Install;

use Bitrix\Main\{Application, Loader};
use Bitrix\Main\Localization\Loc;

/**
 * Class LeftMenu
 * 
 * @package Brix\Install
 */
class LeftMenu
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.accesses";
    const MENU_PATH  = "/.top.menu.php";

    /**
     * Array of processing to be created
     * 
     * @return array
     * [
     *  [
     *      Name,
     *      Path,
     *      [Additional path],
     *      [Additional variables],
     *      Condition
     *  ]
     * ]
     */
    private static function getItems(): array
    {
        return [
            [
                Loc::getMessage("BRIX_ACCESSES_LEFT_MENU_START_NAME"),
                "/brix_accesses/",
                [],
                [],
                "\Bitrix\Main\Loader::includeModule('" . self::MODULE_ID. "') && \Brix\Helpers\Access::getAccess()"
            ],
            [
                Loc::getMessage("BRIX_ACCESSES_LEFT_MENU_HISTORY_NAME"),
                "/brix_accesses/history/",
                [],
                [],
                "\Bitrix\Main\Loader::includeModule('" . self::MODULE_ID. "') && !\Brix\Helpers\Access::getAccess() && \Brix\Helpers\Access::getAccess('history')"
            ]
        ];
    }

    /**
     * Returns the contents of the menu file
     * 
     * @return array
     */
    private static function getMenu(): array
    {
        return \CFileMan::GetMenuArray(Application::getDocumentRoot() . self::MENU_PATH);
    }

    /**
     * Saves changes to the menu
     * 
     * @param array $arMenuItems
     * @param string $menuTemplate
     * @return void
     */
    private static function saveMenu(array $arMenuItems, string $menuTemplate = ""): void
    {
        $siteId = defined(SITE_ID) ? SITE_ID : "s1";
        \CFileMan::SaveMenu([$siteId, self::MENU_PATH], $arMenuItems, $menuTemplate);
    }

    /**
     * Adds items to the left menu
     * 
     * @return void;
     */
    public static function install() {
        if (Loader::includeModule("fileman")) {
            $arResult = self::getMenu();
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"] ?? "";
            $save = false;
            $arCurrentLinks = array_column($arMenuItems, 1);

            foreach (self::getItems() as $item) {
                if (!in_array($item[1], $arCurrentLinks)) {
                    $save = true;
                    $arMenuItems[] = $item;
                }
            }

            if ($save) {
                self::saveMenu($arMenuItems, $menuTemplate);
            }
        }
    }

    /**
     * Deletes items from the left menu
     * 
     * @return void;
     */
    public static function uninstall() {
        if (Loader::includeModule("fileman")) {
            $arResult = self::getMenu();
            $arMenuItems = $arResult["aMenuLinks"];
            $menuTemplate = $arResult["sMenuTemplate"] ?? "";
            $save = false;
            $arItems = array_column(self::getItems(), 1);

            foreach ($arMenuItems as $key => $item) {
                if (in_array($item[1], $arItems)) {
                    $save = true;
                    unset($arMenuItems[$key]);
                }
            }

            if ($save) {
                self::saveMenu($arMenuItems, $menuTemplate);
            }
        }
    }
}