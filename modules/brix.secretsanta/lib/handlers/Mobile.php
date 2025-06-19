<?php
namespace Brix\SecretSanta\Handlers;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Mobile\Tab\Utils;
use Bitrix\MobileApp\Janative\Manager;
use Brix\SecretSanta\Config;
use Brix\SecretSanta\Helpers\Base;

/**
 * Class Mobile
 * 
 * @package Brix\SecretSanta\Handlers
 */
final class Mobile
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * Method for the onMobileMenuStructureBuilt event
     * 
     * @param array $menu
     * @return array
     */
    public static function onMobileMenuStructureBuilt(array $menu): array
    {
        if (
            Base::isModules() && Base::isModuleMobile() && 
            (Base::isRegistration() || Base::isStart()) && 
            Loader::includeModule("mobile") && Loader::includeModule("mobileapp")
        ) {
            $ext = str_replace(".", "-", self::MODULE_ID);
            $code = "brix_activities";
            $arMenuCodes = array_column($menu, "code");
            $gamename = Config::gamename();
            $secretSantaMenu = [
                [
                    "title" => $gamename,
                    "sectionCode" => $code,
                    "sort" => 100,
                    "hidden" => false,
                    "useLetterImage" => true,
                    "imageUrl" => "/bitrix/images/" . $ext . "/gift.png",
                    "color" => "",
                    "params" => [
                        "onclick" => Utils::getComponentJSCode([
                            "name" => "JSStackComponent",
                            "title" => $gamename,
                            "componentCode" => "secretsanta:mobile",
                            "scriptPath" => Manager::getComponentPath("secretsanta:mobile"),
                            "rootWidget" => [
                                "name" => "layout",
                                "settings" => [
                                    "objectName" => "layout",
                                    "useLargeTitleMode" => true,
                                    "backgroundColor" => ""
                                ],
                            ],
                            "params" => [
                                "isShowSectionsBlock" => true,
                            ]
                        ])
                    ]
                ]
            ];

            if (in_array($code, $arMenuCodes)) {
                $strBrixActive = array_search($code, $arMenuCodes);
                $items = &$menu[$strBrixActive]["items"];
                array_splice($items, 0, 0, $secretSantaMenu);
            } else {
                $menu[] = [
                    "title" => Loc::getMessage("BRIX_SECRETSANTA_HANDLERS_MOBILE_SECTION_NAME"),
                    "code" => $code,
                    "sort" => 33,
                    "hidden" => false,
                    "items" => $secretSantaMenu
                ];
            }
        }

        return $menu;
    }
}