<?php
define("STOP_STATISTICS", true);
define("NO_AGENT_CHECK", true);

use Bitrix\Main\{Context, Loader};
use Bitrix\Main\Localization\Loc;
use Brix\Config;
use Brix\Helpers\{Access, UserGroup};
Loc::loadMessages(__FILE__);

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage("BRIX_ACCESSES_OPTIONS_SETTINGS"));

$module_id = "brix.accesses";
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);
$path = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php";

if ($moduleAccessLevel <= "D") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

Loader::IncludeModule($module_id);

$request = Context::getCurrent()->getRequest();
$settingsUrl = $APPLICATION->GetCurPage() . "?lang=" . LANGUAGE_ID . "&mid=" . $module_id;
$userGroups = UserGroup::getGroupList();
$rightsList = Access::getRights();
$arAccess = [["note" => Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_RIGHTS_INFO")]];

foreach ($rightsList as $key => $name) {
    $arAccess[] = [
        $key,
        $name . "<br>". BeginNote() . Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_RIGHTS_INFO_" . strtoupper($key)) . EndNote(),
        "left",
        ["multiselectbox", $userGroups],
    ];
}

$arTabs = [
    [
        "DIV" => "tab1",
        "TAB" => Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_SETTINGS"),
        "TITLE" => Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_SETTINGS_TITLE"),
        "OPTIONS" => [
            ["historysave", Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_SETTINGS_HISTORY_SAVE"), "Y", ["checkbox"]],
            ["historyclear", Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_SETTINGS_HISTORY_CLEAR"), "left", ["selectbox", Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_SETTINGS_HISTORY_CLEAR_OPTIONS")]]
        ]
    ],
    [
        "DIV" => "tab2",
        "TAB" => Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_RIGHTS_TITLE"),
        "OPTIONS" => $arAccess
    ],
    [
        "DIV" => "tab3",
        "TAB" => Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_RIGHTS_MODULE"),
        "TITLE" => Loc::getMessage("BRIX_ACCESSES_OPTIONS_TAB_RIGHTS_MODULE_TITLE")
    ]
];

$tabControl = new CAdminTabControl("tabControl", $arTabs, true, true);

if ($request->isPost() && $moduleAccessLevel === "W" && check_bitrix_sessid()) {
    if ($request["Update"]) {
        foreach ($arTabs as $arTab) {
            foreach ($arTab["OPTIONS"] as $arOption) {
                if (!is_array($arOption) || empty($arOption[0])) {
                    continue;
                }

                $method = "set" . ucfirst(preg_replace("/[^A-Za-z0-9 ]/", "", $arOption[0]));
                $optionValue = $request->getPost($arOption[0]) ?? "";
                Config::$method($optionValue);
            }
        }

        ob_start();
        require_once $path;
        ob_end_clean();

        LocalRedirect($settingsUrl);
    } elseif ($request["default"]) {
        $APPLICATION->DelGroupRight($module_id);
        Config::setDefaultOptions();
    }
}

$tabControl->Begin();
?>
<form method="POST" action="<?= $APPLICATION->GetCurPage(); ?>?mid=<?= htmlspecialcharsbx($mid); ?>&lang=<?= LANGUAGE_ID ?>">
    <?= bitrix_sessid_post(); ?>
    <?
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
        <input class="adm-btn-save" type="submit" name="Update" value="<?= (Loc::GetMessage("BRIX_ACCESSES_OPTIONS_BTN_SAVE")); ?>">
        <input type="submit" name="default" value="<?= (Loc::GetMessage("BRIX_ACCESSES_OPTIONS_BTN_DEFAULT")); ?>" />
    <?}?>
</form>
<? $tabControl->End(); ?>
