<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

/**
 * @var CMain $APPLICATION
 * @var string $templateFolder
 */

Extension::load(["ui.buttons", "ui.forms"]);

if ((int) $arResult["STEP"] === 3) {
    Extension::load(["brix_access", "brix_modal"]);
}

$this->setFrameMode(true);
$messages = Loc::loadLanguageFile(__FILE__);
$step = ((int) $arResult["STEP"] < 3) ? $arResult["STEP"] + 1 : $arResult["STEP"];
$buttons = [];
$iblockMany = $arResult["IBLOCK_MANY"] ? explode(",", $arResult["IBLOCK_MANY"]) : [];
$disabled = !$arResult["CHANGE"] ? "disabled" : "";

if (
    ((int) $arResult["STEP"] > 1 && $arResult["CHANGE"]) ||
    (int) $arResult["STEP"] > 2
) {
    $buttons[] = [
        "TYPE" => "custom",
        "LAYOUT" => '<button class="ui-btn ui-btn-lg accesses-settings-panel__btn" id="accesses-settings-back" type="submit">' . Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_BACK") . '</button>'
    ];
}

if ((int) $arResult["STEP"] < 3) {
    $buttons[] = [
        "TYPE" => "custom",
        "LAYOUT" => '<button class="ui-btn ui-btn-lg ui-btn-success accesses-settings-panel__btn" id="accesses-settings-forward" type="submit">' . Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_FORWARD") . '</button>'
    ];
} else if ($arResult["CHANGE"]) {
    $buttons[] = [
        "TYPE" => "custom",
        "LAYOUT" => '<button class="ui-btn ui-btn-lg ui-btn-success accesses-settings-panel__btn" id="accesses-settings-save" type="submit">' . Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_SAVE") . '</button>'
    ];
}
?>
<form class="accesses-settings" id="accesses-settings-form" action="<?= $arResult["SEF_FOLDER"] ?>" method="POST">
    <div class="accesses-settings__container">
        <input name="STEP" type="hidden" value="<?= $step ?>">
        <?if ((int) $arResult["STEP"] === 1) {?>
            <label class="accesses-settings__label accesses-settings__label_check ui-ctl ui-ctl-wa">
                <input class="accesses-settings__input accesses-settings__input_checkbox" name="COMMON" type="checkbox"  value="Y" <?= ($arResult["COMMON"] === "Y") ? "checked" : "" ?>>
                <span class="accesses-settings__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_CHECKBOX") ?></span>
            </label>
            <div class="accesses-settings__alert ui-alert">
                <span class="accesses-settings__text ui-alert-message"><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_ALERT"); ?></span>
            </div>
        <?} else {?>
            <input name="COMMON" type="hidden" value="<?= $arResult["COMMON"] ?>">

            <?if ((int) $arResult["STEP"] === 2) {
                $keyError = ($arResult["COMMON"] === "Y") ? "many" : "one";
            ?>
                <div class="accesses-settings__alert ui-alert ui-alert-danger ui-alert-icon-danger d-none">
                    <span class="accesses-settings__text ui-alert-message"><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_ALERT_DANGER")[$keyError]; ?></span>
                </div>
                <?if ($arResult["CHANGE"] && $arResult["COMMON"] === "Y") {?>
                    <div class="accesses-settings__block">
                        <label class="accesses-settings__label ui-ctl ui-ctl-wa" for="IBLOCK">
                            <span class="accesses-settings__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_LABEL_IBLOCK") ?></span>
                        </label>
                        <div class="accesses-settings__tagselector" id="iblock_selector" tabindex="0">
                            <input id="IBLOCK" name="IBLOCK" type="hidden" value="<?= $arResult["IBLOCK"] ?>">
                        </div>
                    </div>
                <?}?>

                <div class="accesses-settings__block">
                    <label class="accesses-settings__label ui-ctl ui-ctl-wa" for="IBLOCK_MANY">
                        <span class="accesses-settings__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_LABEL_IBLOCK_MANY") ?></span>
                    </label>
                    <div class="accesses-settings__tagselector" id="iblock_many_selector" tabindex="0">
                        <input id="IBLOCK_MANY" name="IBLOCK_MANY" type="hidden" value="<?= $arResult["IBLOCK_MANY"] ?>">
                    </div>
                </div>
            <?} else {?>
                <input name="IBLOCK" type="hidden" value="<?= $arResult["IBLOCK"] ?>">
                <input name="IBLOCK_MANY" type="hidden" value="<?= $arResult["IBLOCK_MANY"] ?>">

                <?if ($arResult["ACCESS_INFO"]["IBLOCKS"]) {?>
                    <?if ($arResult["COMMON"] === "Y") {
                        $name = $arResult["ACCESS_INFO"]["IBLOCKS"][(int) $arResult["IBLOCK"]]["NAME"] . " [" . $arResult["ACCESS_INFO"]["IBLOCKS"][(int) $arResult["IBLOCK"]]["ID"] ."]";
                        $names = array_column($arResult["IBLOCK_MANY_INFO"]["IBLOCKS"], "NAME");
                        $names = array_map(function($ib) {
                            return $ib["NAME"] . " [" . $ib["ID"] . "]";
                        }, $arResult["IBLOCK_MANY_INFO"]["IBLOCKS"]);
                    ?>
                        <p class="accesses-settings__name accesses-settings__text"><span><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_LABEL_IBLOCK") ?>:</span> <?= $name ?></p>
                        <p class="accesses-settings__name accesses-settings__text"><span><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_LABEL_IBLOCK_MANY_STEP") ?>:</span> <?= implode(", ", $names) ?></p>
                    <?}?>
                    <div class="accesses-settings__iblocks">
                        <?foreach ($arResult["ACCESS_INFO"]["IBLOCKS"] as $iblock) {
                            if (!empty($arResult["IBLOCK"]) && (int) $iblock["ID"] !== (int) $arResult["IBLOCK"]) {
                                continue;
                            }
                        ?>
                            <details class="accesses-settings__ib" id="ib<?= $iblock["ID"] ?>" open>
                                <summary class="accesses-settings__ib-summary accesses-settings__text"><span><?= $iblock["NAME"] ?> [<?= $iblock["ID"] ?>]</span></summary>
                                <div class="accesses-settings__ib-container">
                                    <label class="accesses-settings__label accesses-settings__label_check ui-ctl ui-ctl-wa">
                                        <input class="accesses-settings__input accesses-settings__input_checkbox" name="EXTENDED_<?= $iblock["ID"] ?>" type="checkbox"  value="Y" <?= ($iblock["EXTENDED"] === "Y") ? "checked" : "" ?> <?= $disabled ?>>
                                        <span class="accesses-settings__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_ACCESSES_SETTINGS_TEMPLATE_LABEL_RIGHTS_MODE") ?></span>
                                    </label>
                                    <div id="ib_<?= $iblock["ID"] ?>"></div>
                                </div>
                            </details>
                        <?}?>
                    </div>
                <?}?>
                <?if ($arResult["CHANGE"]) {?>
                <?}?>
            <?}?>
        <?}?>
    </div>
    <?php
    $APPLICATION->IncludeComponent("bitrix:ui.button.panel", "", [
        "ID" => "accesses-settings-panel",
        "ALIGN" => "left",
        "BUTTONS" => $buttons
    ]);
    ?>
</form>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        "use strict";

        BX.message(<?= Json::encode($messages) ?>);
        
        new BrixAccessesSettings({
            step: <?= $arResult["STEP"] ?>,
            isChange: '<?= $arResult["CHANGE"] ? "Y" : "N" ?>',
            common: '<?= $arResult["COMMON"] ?>',
            iblockId: '<?= $arResult["IBLOCK"] ?>',
            iblockIdMany: '<?= $arResult["IBLOCK_MANY"] ?>',
            tagSelectorData: <?= Json::encode($arResult["TAGSELECTOR"]) ?>,
            accessInfo: <?= Json::encode($arResult["ACCESS_INFO"]) ?>
        });
    });
</script>