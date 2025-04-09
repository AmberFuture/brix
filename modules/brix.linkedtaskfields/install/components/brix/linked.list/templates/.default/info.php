<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

$APPLICATION->ShowHead(false);
$dir = Context::getCurrent()->getRequest()->getRequestedPageDirectory();
Extension::load("ui.sidepanel-content");
$asset->addCss($dir . "info.css");
?>
<div class="brix-info ui-slider-section">
    <h1 class="ui-slider-title ui-slider-heading-box"><?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_TITLE") ?></h1>
    <?php
    for ($i = 1; $i <= 9; $i++) {?>
        <details class="brix-info__details">
            <summary class="brix-info__summary"><?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_QUESTION_{$i}") ?></summary>
            <div class="brix-info__container">
                <?= Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_TEMPLATES_INFO_ANSWER_{$i}") ?>
            </div>
        </details>
    <?php
    }
    ?>
</div>
