<?php
define("STOP_STATISTICS", true);
define("NO_AGENT_CHECK", true);

use Bitrix\Main\{Context, Loader};
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Extension;
use Brix\SecretSanta\Config;
use Brix\SecretSanta\Tables\SecretSantaTable;

Extension::load("ui.notification");

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_SETTINGS"));

$module_id = "brix.secretsanta";
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);
$path = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";

if ($moduleAccessLevel <= "D") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loader::IncludeModule($module_id);

$errorCollection = [];
$request = Context::getCurrent()->getRequest();
$settingsUrl = $APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID . "&mid=" . $module_id;
$isBtnDefault = true;
$dateTimeFormat = DateTime::getFormat();
$dateTimeFormat = str_replace([":ss", ":s"], "", $dateTimeFormat);
$dtR = Config::dateregistration() ? DateTime::createFromTimestamp(Config::dateregistration())->format("Y-m-d") . "T" . DateTime::createFromTimestamp(Config::dateregistration())->format("H:i") : "";
$dateRegistration = '<input name="dateregistration" type="datetime-local" value="' . $dtR . '">';
$dtS = Config::datestart() ? DateTime::createFromTimestamp(Config::datestart())->format("Y-m-d") . "T" . DateTime::createFromTimestamp(Config::datestart())->format("H:i") : "";
$dateStart = '<input name="datestart" type="datetime-local" value="' . $dtS . '">';
$dtN = Config::noticedate() ? DateTime::createFromTimestamp(Config::noticedate())->format("Y-m-d") . "T" . DateTime::createFromTimestamp(Config::noticedate())->format("H:i") : "";
$noticeDate = '<input name="noticedate" type="datetime-local" value="' . $dtN . '">';
$dtC = Config::datecompletion() ? DateTime::createFromTimestamp(Config::datecompletion())->format("Y-m-d") . "T" . DateTime::createFromTimestamp(Config::datecompletion())->format("H:i") : "";
$datecompletion = '<input name="datecompletion" type="datetime-local" value="' . $dtC . '">';
$start = false;
$unallocated = [];

if (
    !empty(Config::dateregistration()) && Config::dateregistration() <= strtotime(new DateTime())
) {
    $dateRegistration = "<b>" . DateTime::createFromTimestamp(Config::dateregistration())->format($dateTimeFormat) . "</b>";
    $isBtnDefault = false;
}

if (
    !empty(Config::datestart()) && Config::datestart() <= strtotime(new DateTime())
) {
    $dateStart = "<b>" . DateTime::createFromTimestamp(Config::datestart())->format($dateTimeFormat) . "</b>";
    $start = true;
}

$arTabSet = [
    [
        "gamename",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_GAME_NAME"),
        Config::gamename(),
        ["text", 50, 100]
    ],
    [
        "opt_dateregistration",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_DATE_REGISTRATION"),
        $dateRegistration,
        ["statichtml"]
    ],
    [
        "opt_datestart",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_DATE_START"),
        $dateStart,
        ["statichtml"]
    ],
    [
        "opt_datecompletion",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_DATE_COMPLETION"),
        $datecompletion,
        ["statichtml"]
    ]
];

if (!$isBtnDefault) {
    $arTabSet[] = ["note" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_COUNT_PLAYERS", ["#COUNT#" => count(SecretSantaTable::getTakeParts())])];
}

if ($start) {
    $unallocated = SecretSantaTable::checkUnallocatedPlayers();

    if (count($unallocated) > 0) {
        $arTabSet[] = [
            "",
            "<br>" . Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_COUNT_UNALLOCATED_PLAYERS", ["#COUNT#" => count($unallocated)]),
            '<input class="adm-btn-brix adm-btn-green" type="button" value="' . Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_BTN_UNALLOCATED_PLAYERS") . '">',
            ["statichtml"]
        ];

        $arTabSet[] = ["note" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TEXT_UNALLOCATED_PLAYERS")];
    }
}


$arTabNotif = [
    [
        "note" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_NOTIFICATIONS_INFO")
    ],
    [
        "noticeregistration",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_NOTICE_REGISTRATION"),
        Config::noticeregistration(),
        ["textarea", 10, 80]
    ],
    [
        "noticewish",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_NOTICE_WISH"),
        Config::noticewish(),
        ["textarea", 10, 80]
    ],
    [
        "noticestart",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_NOTICE_START"),
        Config::noticestart(),
        ["textarea", 10, 80]
    ],
    [
        "opt_noticedate",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_NOTICE_DATE"),
        $noticeDate,
        ["statichtml"]
    ],
    [
        "noticeadditional",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_NOTICE_ADDITIONAL"),
        Config::noticeadditional(),
        ["textarea", 10, 80]
    ],
    [
        "enablenoticecompletion",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_ENABLE_NOTICE_COMPLETION"),
        "Y",
        ["checkbox"]
    ],
    [
        "noticecompletion",
        Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_NOTICE_COMPLETION"),
        Config::noticecompletion(),
        ["textarea", 10, 80]
    ]
];

$arTabs = [
    [
        "DIV" => "tab1",
        "TAB" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS"),
        "TITLE" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_SETTINGS_TITLE"),
        "OPTIONS" => $arTabSet
    ],
    [
        "DIV" => "tab2",
        "TAB" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_NOTIFICATIONS"),
        "TITLE" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_NOTIFICATIONS"),
        "OPTIONS" => $arTabNotif
    ],
    [
        "DIV" => "tab3",
        "TAB" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_RIGHTS_MODULE"),
        "TITLE" => Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_TAB_RIGHTS_MODULE_TITLE")
    ]
];

$tabControl = new CAdminTabControl("tabControl", $arTabs, true, true);

if ($request->isPost() && $moduleAccessLevel === "W" && check_bitrix_sessid()) {
    if ($request["Update"]) {
        $postList = $request->getPostList()->toArray();

        foreach ($arTabs as $arTab) {
            foreach ($arTab["OPTIONS"] as $arOption) {
                if (!is_array($arOption) || empty($arOption[0])) {
                    continue;
                }

                $optName = str_starts_with($arOption[0], "opt_") ? str_replace("opt_", "", $arOption[0]) : $arOption[0];
                $isCheckbox = ($arOption[3][0] === "checkbox");
                $method = "set" . ucfirst(preg_replace("/[^A-Za-z0-9 ]/", "", $optName));
                $optionValue = isset($postList[$optName]) ? $postList[$optName] : ($isCheckbox ? "N" : null);
                $save = true;

                if ($optName && $optionValue !== null) {
                    if (
                        $optName === "dateregistration" && !empty($optionValue) && 
                        strtotime($optionValue) <= strtotime(new DateTime())
                    ) {
                        $save = false;
                        $errorCollection[] = Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_ERROR_DATE_REGISTRATION");
                    } elseif (in_array($optName, ["datestart", "datecompletion", "noticedate"]) && !empty($optionValue)) {
                        if (empty(Config::dateregistration())) {
                            $save = false;
                            $errorCollection[] = Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_ERROR_NOT_DATE_REGISTRATION", ["#OPTION#" => $arOption[1]]);
                        } elseif (in_array($optName, ["datestart", "noticedate"])) {
                            if (strtotime($optionValue) <= Config::dateregistration()) {
                                $save = false;
                                $errorCollection[] = Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_ERROR_LESS_DATE_REGISTRATION", ["#OPTION#" => $arOption[1]]);
                            }
                            if (!empty($postList["datecompletion"]) && strtotime($optionValue) >= strtotime($postList["datecompletion"])) {
                                $save = false;
                                $errorCollection[] = Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_ERROR_MORE_DATE_REGISTRATION", ["#OPTION#" => $arOption[1]]);
                            }
                        } elseif ($optName === "datecompletion" && strtotime($optionValue) <= Config::dateregistration()) {
                            $save = false;
                            $errorCollection[] = Loc::getMessage("BRIX_SECRETSANTA_OPTIONS_ERROR_LESS_DATE_REGISTRATION", ["#OPTION#" => $arOption[1]]);
                        }
                    }

                    if ($save) {
                        Config::$method($optionValue);
                    }
                }
            }
        }

        ob_start();
        require_once $path;
        ob_end_clean();

        if (!empty($errorCollection)) {
            Config::setErrors($errorCollection);
        }

        LocalRedirect($settingsUrl);
    } elseif ($request["default"]) {
        $APPLICATION->DelGroupRight($module_id);
        Config::setDefaultOptions();
        LocalRedirect($settingsUrl);
    }
}

if (Config::errors()) {
    foreach (Config::errors() as $error) {
        ShowError($error);
    }

    Config::delete("errors");
}

$tabControl->Begin();
?>
<form method="POST" action="<?= $APPLICATION->GetCurPage(); ?>?mid=<?= htmlspecialcharsbx($mid); ?>&lang=<?= LANGUAGE_ID ?>">
    <?= bitrix_sessid_post(); ?>
    <?php
    foreach($arTabs as $arTab) {
        if ($arTab["OPTIONS"]) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $arTab["OPTIONS"]);
        }
    }

    $tabControl->BeginNextTab();
    require_once $path;
    $tabControl->Buttons();
    ?>
    <?if ($moduleAccessLevel === "W") {?>
        <input class="adm-btn-save" type="submit" name="Update" value="<?= (Loc::GetMessage("BRIX_SECRETSANTA_OPTIONS_BTN_SAVE")); ?>">
        <?if ($isBtnDefault) {?>
            <input type="submit" name="default" value="<?= (Loc::GetMessage("BRIX_SECRETSANTA_OPTIONS_BTN_DEFAULT")); ?>" />
        <?}?>
    <?}?>
    <?if (count($unallocated) > 0) {?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", () => {
                "use strict";

                document.querySelector(".adm-btn-brix").addEventListener("click", () => {
                    BX.ajax.runAction("brix:secretsanta.Secretsanta.distribution", {
                        method: "post"
                    }).then((response) => {
                        let events = !response.data.error ? {
                            onClose: (event) => {
                                location.reload();
                            }
                        } : {};

                        BX.UI.Notification.Center.notify({
                            position: "top-right",
                            autoHideDelay: 5000,
                            events: events,
                            content: response.data.text
                        });
                    }, (response) => {
                        console.error(response);
                        let textError = response.errors[0] ? response.errors[0].message : "An error has occurred";
                        BX.UI.Notification.Center.notify({
                            position: "top-right",
                            autoHideDelay: 5000,
                            content: textError
                        });
                    });
                });
            });
        </script>
    <?}?>
</form>
<?php $tabControl->End(); ?>
