<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>
<?php
$APPLICATION->IncludeComponent(
    "brix:accesses",
    "",
    [
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/brix_accesses/",
        "SEF_URL_TEMPLATES" => [
            "history" => "history/"
        ],
    ],
    false
);
?>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>