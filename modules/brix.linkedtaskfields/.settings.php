<?php
$moduleId = "brix.linkedtaskfields";

return [
    "ui.entity-selector" => [
        "value" => [
            "entities" => [
                [
                    "entityId" => "brix_task_tag",
                    "provider" => [
                        "moduleId" => $moduleId,
                        "className" => "\\Brix\\Api\\Providers\\TaskTagProvider"
                    ]
                ],
                [
                    "entityId" => "brix_enumeration",
                    "provider" => [
                        "moduleId" => $moduleId,
                        "className" => "\\Brix\\Api\\Providers\\EnumerationProvider"
                    ]
                ]
            ],
            "extensions" => ["brix-linkedtaskfields.entity-selector"]
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