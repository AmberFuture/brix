<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Web\Uri;

/** @global CMain $APPLICATION */

$userId = CurrentUser::get()->getId();
$request = Context::getCurrent()->getRequest();
$uri = new Uri($request->getRequestUri());
$path = $uri->getPath();
$backUrl = (str_contains($path, "/company/personal/user/") || str_contains($path, "/workgroups/group/")) ? $uri->getUri() : "/company/personal/user/{$userId}/tasks/";
$getParams = $request->getQueryList()->toArray();
?>
<?php
$APPLICATION->IncludeComponent(
    "bitrix:ui.sidepanel.wrapper",
    "",
    [
        "POPUP_COMPONENT_NAME" => "brix:linked.detail",
        "POPUP_COMPONENT_TEMPLATE_NAME" => "",
        "POPUP_COMPONENT_PARAMS" => $getParams,
        "USE_UI_TOOLBAR" => "Y",
        "USE_BACKGROUND_CONTENT" => false,
        "USE_PADDING" => false,
        "PAGE_MODE" => false,
        "PAGE_MODE_OFF_BACK_URL" => $backUrl
    ]
);
?>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>