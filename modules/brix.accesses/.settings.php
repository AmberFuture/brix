<?php
$moduleId = "brix.accesses";

return [
    "ui.entity-selector" => [
        "value" => [
            "entities" => [
                [
                    "entityId" => "brix_iblocks",
                    "provider" => [
                        "moduleId" => $moduleId,
                        "className" => "\\Brix\\Api\\Providers\\IblockProvider"
                    ]
                ]
            ],
            "extensions" => ["brix-accesses.entity-selector"]
        ],
        "readonly" => true
    ],
    "controllers" => [
        "value" => [
            "defaultNamespace" => "\\Brix\\Api\\Controllers",
            "namespaces" => [
                "\\Brix\\Api\\Controllers" => "api"
            ]
        ],
        "readonly" => true
    ]
];
?>