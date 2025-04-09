<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;

/**
 * @var CMain $APPLICATION
 * @var string $templateFolder
 */

$this->setFrameMode(true);

Extension::load(["ui.alerts", "ui.entity-selector", "ui.forms", "ui.select"]);

$messages = Loc::loadLanguageFile(__FILE__);
$name = (!empty($arResult["FIELD_NAME"]) && !empty($arResult["INFO"]["LABEL"])) ? $arResult["INFO"]["LABEL"] : $arResult["FIELD_NAME"];
$active = (!empty($arResult["FIELD_NAME"]) && $arResult["INFO"]["ACTIVE"] === "N") ? "" : "checked";
$required = (!empty($arResult["FIELD_NAME"]) && $arResult["INFO"]["REQUIRED"] === "Y") ? "checked" : "";
$alert = (empty($arResult["FIELD_NAME"]) || $arResult["INFO"]["MANDATORY"] === "N") ? "d-none" : "";
$currentConditions = !empty($arResult["FIELD_NAME"]) ? $arResult["INFO"]["CONDITIONS"] : [];
?>
<div class="brix-loader"></div>
<form class="linked-detail" id="linked-detail-form" action="/" method="POST">
    <div class="linked-detail__container">
        <div class="ui-alert ui-alert-icon-warning d-none" id="linked-detail-error">
            <span class="linked-detail__text ui-ctl-label-text"></span>
        </div>
        <div class="ui-alert ui-alert-danger ui-alert-icon-danger <?= $alert ?>" id="linked-detail-mandatory">
            <span class="linked-detail__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_ALERT_REQUIRED") ?></span>
        </div>
        <div class="linked-detail__field">
            <div class="linked-detail__label linked-detail__field_grid ui-ctl ui-ctl-wa">
                <input name="FIELD_NAME" type="hidden" value="<?= $arResult["FIELD_NAME"] ?>">
                <span class="linked-detail__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_FIELD_NAME") ?></span>
                <?php
                if (!empty($arResult["FIELD_NAME"])) {
                ?>
                    <span class="linked-detail__text linked-detail__text_bold ui-ctl-label-text"><?= $name ?></span>
                <?php
                } else {
                ?>
                    <div data-name="FIELD_NAME"></div>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="linked-detail__field">
            <label class="linked-detail__label linked-detail__label_check ui-ctl ui-ctl-wa">
                <input class="linked-detail__input linked-detail__input_checkbox" name="ACTIVE" type="checkbox" value="Y" <?= $active ?>>
                <span class="linked-detail__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_ACTIVE") ?></span>
            </label>
        </div>
        <div class="linked-detail__field">
            <label class="linked-detail__label linked-detail__label_check ui-ctl ui-ctl-wa">
                <input class="linked-detail__input linked-detail__input_checkbox" name="REQUIRED" type="checkbox" value="Y" <?= $required ?>>
                <span class="linked-detail__text ui-ctl-label-text"><?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_REQUIRED") ?></span>
            </label>
        </div>
        <div class="linked-detail__field linked-detail__field_last d-none" id="all-rules">
            <div class="linked-detail__rules"></div>
            <button class="ui-btn ui-btn-md ui-btn-primary-dark ui-btn-icon-add" id="linked-detail-add" type="button"><?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_BTN_ADD_RULES") ?></button>
        </div>
    </div>
    <?php
    $APPLICATION->IncludeComponent("bitrix:ui.button.panel", "", [
        "ID" => "linked-detail-panel",
        "ALIGN" => "left",
        "BUTTONS" => [
            [
                "TYPE" => "custom",
                "LAYOUT" => '<button class="ui-btn ui-btn-success" id="linked-detail-save" name="save" type="submit">' . Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_BTN_SAVE") . '</button>'
            ],
            [
                "TYPE" => "custom",
                "LAYOUT" => '<button class="ui-btn ui-btn-light-border" id="linked-detail-close" name="close" type="button">' . Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_BTN_CLOSE") . '</button>'
            ]
        ]
    ]);
    ?>
</form>
<template id="conditions">
    <div class="linked-detail__cond">
        <button class="linked-detail__cond-del" type="button" title="<?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_DETAIL_TEMPLATE_BTN_DELETE_RULES") ?>"></button>
        <div class="linked-detail__cond-field">
            <input name="CONDITIONS[#ID#][FIELD]" type="hidden" value="">
            <div data-name="CONDITIONS[#ID#][FIELD]"></div>
        </div>
        <div class="linked-detail__cond-type">
            <input name="CONDITIONS[#ID#][TYPE]" type="hidden" value="">
            <div data-name="CONDITIONS[#ID#][TYPE]"></div>
        </div>
        <div class="linked-detail__cond-val">
            <input name="CONDITIONS[#ID#][VALUES]" type="hidden" value="">
            <div data-name="CONDITIONS[#ID#][VALUES]"></div>
        </div>
    </div>
</template>
<template id="additional">
    <div class="linked-detail__additional">
        <input name="ADDITIONAL[#ID#]" type="hidden" value="">
        <div data-name="ADDITIONAL[#ID#]"></div>
    </div>
</template>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        "use strict";

        BX.message(<?= Json::encode($messages) ?>);
        
        new BrixLinkedDetail({
            allFields: <?= Json::encode($arResult["ALL_FIELDS"]) ?>,
            conditions: <?= Json::encode($arResult["CONDITIONS"]) ?>,
            currentConditions: <?= Json::encode($currentConditions) ?>,
            fieldName: "<?= $arResult["FIELD_NAME"] ?>",
            fieldsMandatory: <?= Json::encode($arResult["LIST_FIELD_MANDATORY"]) ?>,
            listField: <?= Json::encode($arResult["LIST_FIELD"]) ?>
        });
    });
</script>
