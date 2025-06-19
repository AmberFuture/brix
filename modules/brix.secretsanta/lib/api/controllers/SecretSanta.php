<?php
namespace Brix\SecretSanta\Api\Controllers;

use Bitrix\Main\Engine\{Controller, CurrentUser};
use Bitrix\Main\Engine\ActionFilter\{Authentication, CloseSession, HttpMethod};
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\{Date, DateTime};
use Bitrix\Main\UI\Extension;
use Brix\SecretSanta\Config;
use Brix\SecretSanta\Helpers\{Base, TextSanitizer, User};
use Brix\SecretSanta\Tables\SecretSantaTable;

/**
* Class SecretSanta
* 
* @package Brix\SecretSanta\Api\Controllers
*/
final class SecretSanta extends Controller
{
    /**
     * Configures ajax actions
     * 
     * @return array
     */
    public function configureActions(): array
    {
        $configuration = [];

        foreach (["getConfig", "getPlayer", "distribution", "updateTakePart", "updateWishlist"] as $action) {
            $configuration[$action] = [
                "+prefilters" => [
                    new CloseSession(),
                    new Authentication(),
                    new HttpMethod([
                        HttpMethod::METHOD_GET,
                        HttpMethod::METHOD_POST
                    ])
                ]
            ];
        }

        return $configuration;
    }

    /**
     * Getting basic information about the game
     * 
     * @return array
     */
    public static function getConfigAction(): array
    {
        $arResult = [];
        $dateTimeFormat = DateTime::getFormat();
        $dateTimeFormat = str_replace([":SS", ":ss", ":s"], ["", "", ""], $dateTimeFormat);
        $dateStart = Config::datestart() ? DateTime::createFromTimestamp(Config::datestart())->format($dateTimeFormat) : "";
        $dateEnd = Config::datecompletion() ? DateTime::createFromTimestamp(Config::datecompletion())->format(Date::getFormat()) : "";
        $gamename = Config::gamename();
        $arResult = [
            "gamename" => $gamename,
            "notPart" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_NOT_PART", ["#GAMENAME#" => $gamename]),
            "notCheckPart" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_NOT_CHECK_PART", ["#GAMENAME#" => $gamename]),
            "datestart" => $dateStart,
            "dateend" => $dateEnd,
            "textstart" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_DATESTART"),
            "textend" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_DATEEND"),
            "mywish" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_MYWISH"),
            "wishempty" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_WISH_EMPTY"),
            "recipient" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_RECIPIENT"),
            "recipientwich" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_RECIPIENT_WISH"),
            "confirm" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_BTN_CONFIRM"),
            "cancel" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_BTN_CANCEL"),
            "edit" => Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_BTN_EDIT"),
            "bxeditor" => [
                "actionUrl" => "/bitrix/admin/fileman_html_editor_action.php",
                "allowPhp" => false,
                "askBeforeUnloadPage" => false,
                "autoResize" => true,
                "bbCode" => false,
                "cleanEmptySpans" => true,
                "components" => false,
                "copilotParams" => null,
                "cssIframePath" => \CUtil::GetAdditionalFileURL("/bitrix/js/fileman/html_editor/iframe-style.css"),
                "controlsMap" => [
                    ["id" => "Bold", "compact" => true, "sort" => 1],
                    ["id" => "Color", "compact" => true, "sort" => 2],
                    ["id" => "OrderedList", "compact" => true, "sort" => 3],
                    ["id" => "UnorderedList", "compact" => true, "sort" => 4],
                    ["id" => "InsertLink", "compact" => true, "sort" => 5],
                    ["id" => "RemoveFormat", "compact" => true, "sort" => 6],
                    ["id" => "Smile", "compact" => true, "sort" => 7]
                ],
                "designTokens" => Extension::getHtml("ui.design-tokens"),
                "fontSize" => "16px",
                "isCopilotEnabled" => false,
                "isCopilotImageEnabledBySettings" => false,
                "isCopilotTextEnabledBySettings" => false,
                "isMentionUnavailable" => false,
                "lazyLoad" => false,
                "lastSpecialchars" => false,
                "limitPhpAccess" => false,
                "minBodyHeight" => 200,
                "minBodyWidth" => 350,
                "normalBodyWidth" => 575,
                "pasteClearTableDimen" => true,
                "pasteSetBorders" => false,
                "pasteSetColors" => true,
                "pasteSetDecor" => false,
                "showComponents" => false,
                "showNodeNavi" => false,
                "showSnippets" => false,
                "showTaskbars" => false,
                "smiles" => [],
                "smilesSet" => "",
                "spellcheck_path" => \CUtil::GetAdditionalFileURL("/bitrix/js/fileman/html_editor/html-spell.js"),
                "taskbarShown" => false,
                "templateId" => "bitrix24",
                "templateParams" => [],
                "templates" => [
                    "bitrix24" => []
                ],
                "useFileDialogs" => false,
                "width" => "99%"
            ]
        ];

        if (Loader::includeModule("forum")) {
            $arResult["bxeditor"]["smileSet"] = \CForumSmile::getSetsByType("S", LANGUAGE_ID);
            $arSmiles = \CForumSmile::getSmiles("S", LANGUAGE_ID);
            foreach ($arSmiles as $arSmile) {
                $arResult["bxeditor"]["smiles"][] = array_change_key_case($arSmile, CASE_LOWER) + [
                    "path" => $arSmile["IMAGE"],
                    "code" => array_shift(explode(" ", str_replace("////", "//", $arSmile["TYPING"])))
                ];
            }
        }

        return $arResult;
    }

    /**
     * Gets information about a participant
     * 
     * @param bool $isMobile
     * @return array
     */
    public static function getPlayerAction(bool $isMobile = false): array
    {
        $arResult = [];

        if (Base::isModules()) {
            $type = Base::isRegistration() ? "registration" : (Base::isStart() ? "start" : false);
            $userId = CurrentUser::get()->getId();

            if ($userId && !!$type) {
                $arResult = [
                    "player" => SecretSantaTable::getById($userId)->fetch(),
                    "type" => $type,
                    "recipient" => [
                        "id" => "",
                        "link" => "",
                        "wish" => ""
                    ]
                ];

                if ($arResult["player"]) {
                    if ($isMobile && $arResult["player"]["WISHLIST"] && $type === "registration") {
                        $sanitizer = new TextSanitizer($arResult["player"]["WISHLIST"], $isMobile);
                        $sanitizer->sanitizerMobile();
                        $arResult["player"]["WISHLIST"] = $sanitizer->getMobileTextView();
                        $arResult["player"]["EDIT_WISHLIST"] = $sanitizer->getMobileTextEdit();
                    }
                    
                    if ($type === "start" && $arResult["player"]["RECIPIENT"]) {
                        $id = (int) $arResult["player"]["RECIPIENT"];
                        $recipient = SecretSantaTable::getById($id)->fetch();
                        $user = User::getUserFormated([$id]);
                        $arResult["recipient"]["link"] = $user ? Loc::getMessage("BRIX_SECRETSANTA_CONTROLLER_SECRETSANTA_RECIPIENT_LINK", ["#ID#" => $id, "#NAME#" => $user[$id]]) : "";
                        $arResult["recipient"]["wish"] = $recipient["WISHLIST"] ?? "";

                        if ($isMobile) {
                            $arResult["recipient"]["id"] = $user ? $id : 0;
                            $arResult["recipient"]["link"] = $user ? $user[$id] : "";

                            if ($arResult["recipient"]["wish"]) {
                                $sanitizerRec = new TextSanitizer($arResult["recipient"]["wish"]);
                                $sanitizerRec->sanitizerMobile();
                                $arResult["recipient"]["wish"] = $sanitizerRec->getMobileTextView();
                            }
                        }
                    }

                    if (!$arResult["player"]["WISHLIST"]) {
                        $arResult["player"]["WISHLIST"] = "";
                    }
                }
            }
        }

        return $arResult;
    }

    /**
     * Distributes the players
     * 
     * @return array
     */
    public static function distributionAction(): array
    {
        return SecretSantaTable::distributionUnallocatedPlayers();
    }

    /**
     * Updates the consent for participation
     * 
     * @param string $takePart
     * @return void
     */
    public static function updateTakePartAction(string $takePart): void
    {
        $userId = CurrentUser::get()->getId();
        SecretSantaTable::update($userId, ["TAKE_PART" => $takePart]);
    }

    /**
     * Updates the wishlist
     * 
     * @param string $wishlist
     * @param bool $isMobile
     * @return array
     */
    public static function updateWishlistAction(string $wishlist = "", bool $isMobile = false): array
    {
        $userId = CurrentUser::get()->getId();
        $mobileTextView = "";
        $mobileTextEdit = "";

        if (!empty(trim($wishlist))) {
            if (trim($wishlist) === "<br>") {
                $wishlist = "";
            } else {
                $sanitizer = new TextSanitizer($wishlist, $isMobile);
                $sanitizer->sanitizer();
                $wishlist = $sanitizer->getText();

                if ($isMobile) {
                    $mobileTextView = $sanitizer->getMobileTextView();
                    $mobileTextEdit = $sanitizer->getMobileTextEdit();
                }
            }
        }

        SecretSantaTable::update($userId, ["WISHLIST" => $wishlist ?? null]);

        return ["text" => $isMobile ? $mobileTextView : $wishlist, "editText" => $mobileTextEdit];
    }
}