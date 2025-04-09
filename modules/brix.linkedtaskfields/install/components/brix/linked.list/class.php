<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Error, ErrorCollection, Loader, ModuleManager};
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Grid\Panel\{Actions, Snippet, Types};
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Brix\Helpers\{User, UserFields};
use Brix\Tables\LinkedTaskFieldsTable;
use Bitrix\UI\Buttons\{Button, Color, Icon, JsCode};

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class BrixLinkedListComponent extends CBitrixComponent
{
    /**
     * @var string
     */
    const MODULE_ID = "brix.linkedtaskfields";
    public $id = "brix_linkedtaskfields";
    public $filterId = "brix_linkedtaskfields_filter";

    /**
     * @var array
     */
    public $sort;
    public $filter;
    public $gridOptions;
    public $columns;
    protected $nav;
    protected $filterData;
    protected $navParams;

    /**
     * A collection of errors
     * 
     * @var object
     */
    private ErrorCollection $errors;

    public function executeComponent()
    {
        global $APPLICATION;
        $APPLICATION->SetTitle(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_TITLE"));
        Toolbar::deleteFavoriteStar();
        
        $this->errors = new ErrorCollection();

        if (!Loader::IncludeModule(self::MODULE_ID)) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_ERROR_NOT_MODULE")));
        };
        
        if (!ModuleManager::isModuleInstalled("tasks")) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_ERROR_NOT_MODULE_TASKS")));
        };

        if (!User::isAccess()) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_ERROR_ACCESS")));
        }

        if (!$this->isErrors()) {
            $this->initVariables();
            $this->initFilter();
            $this->getGridFilter();
            $this->checkGridFilterData();
            $this->getPageSizes();
            $this->getGridColumns();
            $this->getActionPanel();

            $this->arResult["GRID_ID"] = $this->id;
            $this->arResult["NAV_OBJECT"] = $this->nav;
            $this->arResult["COLUMNS"] = $this->columns;
            $this->arResult["GRID_ROWS"] = $this->processingQueryResults();

            $this->arResult["POPUP_DELETE"] = [
                "BRIX_LINKED_LIST_TITLE" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_JS_DELETE_TITLE"),
                "BRIX_LINKED_LIST_TEXT" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_JS_DELETE_TEXT"),
                "BRIX_LINKED_LIST_TEXT_MULTI" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_JS_DELETE_MULTI_TEXT"),
                "BRIX_LINKED_LIST_OK" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_JS_DELETE_BTN_OK"),
                "BRIX_LINKED_LIST_CANCEL" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_JS_DELETE_BTN_CANCEL")
            ];

            $this->addFilter();
            $this->addButton();

            $this->includeComponentTemplate();
        }
    }

    /**
     * Processing incoming parameters
     * 
     * @return void
     **/
    protected function initVariables(): void
    {
        $this->gridOptions = new GridOptions($this->id);
        $this->sort = $this->gridOptions->GetSorting(["sort" => ["LANG.EDIT_FORM_LABEL" => "DESC"], "vars" => ["by" => "by", "order" => "order"]]);
        $this->navParams = $this->gridOptions->GetNavParams();
        $this->nav = new PageNavigation($this->id);
        $this->nav->allowAllRecords(true)->setPageSize($this->navParams["nPageSize"])->initFromUri();
        $this->filter = [];
        $this->columns = [];
    }

    /**
     * Processing incoming filter
     * 
     * @return void
     **/
    protected function initFilter()
    {
        $this->arResult["FILTER_ID"] = $this->filterId;
        $fields = UserFields::getListFields(false);
        $arList = $fields ? array_combine(array_column($fields, "FIELD_NAME"), array_column($fields, "LABEL")) : [];
        $this->arResult["FILTER"] = [
            [
                "id" => "FIELD_NAME",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_FIELD_NAME"),
                "type" => "list",
                "items" => $arList,
                "params" => [
                    "multiple" => "Y"
                ],
                "default" => true
            ],
            [
                "id" => "ACTIVE",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTIVE"),
                "type" => "checkbox",
                "default" => true
            ],
            [
                "id" => "REQUIRED",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_REQUIRED"),
                "type" => "checkbox",
                "default" => true
            ],
            [
                "id" => "DATE_CREATE",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_DATE_CREATE"),
                "type" => "date",
                "default" => false
            ],
            [
                "id" => "CREATED_BY",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_CREATED_BY"),
                "type" => "dest_selector",
                "params" => [
                    "multiple" => "Y"
                ],
                "default" => false
            ],
            [
                "id" => "TIMESTAMP_X",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_TIMESTAMP_X"),
                "type" => "date",
                "default" => false
            ],
            [
                "id" => "MODIFIED_BY",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_MODIFIED_BY"),
                "type" => "dest_selector",
                "params" => [
                    "multiple" => "Y"
                ],
                "default" => false
            ]
        ];
    }
    
    /**
     * Prepare and add filter
     *
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function getGridFilter()
    {
        $filterOption = new FilterOptions($this->filterId);
        $this->filterData = $filterOption->getFilter();
    }

    /**
     * Process filter
     *
     * @return void
     */
    protected function checkGridFilterData()
    {
        foreach ($this->filterData as $key => $value) {
            if (!empty($value)) {
                switch ($key) {
                    case "FIND":
                        $this->filter["%FIELD_NAME"] = $value;
                        $this->filter["%LANG.EDIT_FORM_LABEL"] = $value;
                        break;
                    case "FIELD_NAME":
                    case "ACTIVE":
                    case "REQUIRED":
                        $this->filter[$key] = $value;
                        break;
                    case "CREATED_BY":
                    case "MODIFIED_BY":
                        $this->filter["={$key}"] = array_map(function($v)
                        {
                            return str_replace("U", "", $v);
                        }, $value);
                        break;
                    case "DATE_CREATE_from":
                    case "TIMESTAMP_X_from":
                        $key = str_replace("_from", "", $key);
                        $this->filter[">={$key}"] = $value;
                        break;
                    case "DATE_CREATE_to":
                    case "TIMESTAMP_X_to":
                        $key = str_replace("_to", "", $key);
                        $this->filter["<={$key}"] = $value;
                        break;
                    case "PRESET_ID":
                    case "FILTER_ID":
                    case "FILTER_APPLIED":
                        unset($filterData[$key]);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Prepare and add page sizes
     *
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     */
    protected function getPageSizes()
    {
        $this->arResult["PAGE_SIZES"] = [
            ["NAME" => "5", "VALUE" => "5"],
            ["NAME" => "10", "VALUE" => "10"],
            ["NAME" => "20", "VALUE" => "20"],
            ["NAME" => "50", "VALUE" => "50"],
            ["NAME" => "100", "VALUE" => "100"]
        ];
    }
    
    /**
     * Get grid columns
     *
     * @return void
     */
    protected function getGridColumns()
    {
        $this->columns = [
            [
                "id" => "FIELD_NAME",
                "sort" => "LANG.EDIT_FORM_LABEL",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_FIELD_NAME"),
                "default" => true
            ],
            [
                "id" => "ACTIVE",
                "sort" => "ACTIVE",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTIVE"),
                "default" => true
            ],
            [
                "id" => "REQUIRED",
                "sort" => "REQUIRED",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_REQUIRED"),
                "default" => true
            ],
            [
                "id" => "DATE_CREATE",
                "sort" => "DATE_CREATE",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_DATE_CREATE"),
                "default" => true
            ],
            [
                "id" => "CREATED_BY",
                "sort" => "CREATED_BY",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_CREATED_BY"),
                "default" => true
            ],
            [
                "id" => "TIMESTAMP_X",
                "sort" => "TIMESTAMP_X",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_TIMESTAMP_X"),
                "default" => true
            ],
            [
                "id" => "MODIFIED_BY",
                "sort" => "MODIFIED_BY",
                "name" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_MODIFIED_BY"),
                "default" => true
            ]
        ];
    }

    /**
     * Adds a button
     * 
     * @return void
     */
    protected function getActionPanel(): void
    {
        $snippet = new Snippet();
        $activate = $snippet->getApplyButton([
            "ONCHANGE" => [
                [
                    "ACTION" => Actions::CALLBACK,
                    "DATA" => [["JS" => "BrixLinkedList.updateMulti();"]]
                ]
            ]
        ]);
        $deactivate = $snippet->getApplyButton([
            "ONCHANGE" => [
                [
                    "ACTION" => Actions::CALLBACK,
                    "DATA" => [["JS" => "BrixLinkedList.updateMulti('N');"]]
                ]
            ]
        ]);
        $delete = $snippet->getApplyButton([
            "ONCHANGE" => [
                [
                    "ACTION" => Actions::CALLBACK,
                    "DATA" => [["JS" => "BrixLinkedList.deleteMulti();"]]
                ]
            ]
        ]);
        $checkAll = $snippet->getForAllCheckbox();
        $this->arResult["ACTION_PANEL"] = [
            "GROUPS" => [
                "TYPE" => [
                    "ITEMS" => [
                        [
                            "ID" => "delete",
                            "TYPE" => Types::DROPDOWN,
                            "ITEMS" => [
                                ["VALUE" => "", "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_SHOW")],
                                [
                                    "VALUE" => "activate",
                                    "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_ACT"),
                                    "ONCHANGE" => [
                                        [
                                            "ACTION" => Actions::CREATE,
                                            "DATA" => [$activate]
                                        ]
                                    ]
                                ],
                                [
                                    "VALUE" => "deactivate",
                                    "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_DEACT"),
                                    "ONCHANGE" => [
                                        [
                                            "ACTION" => Actions::CREATE,
                                            "DATA" => [$deactivate]
                                        ]
                                    ]
                                ],
                                [
                                    "VALUE" => "delete",
                                    "NAME" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_DELETE"),
                                    "ONCHANGE" => [
                                        [
                                            "ACTION" => Actions::CREATE,
                                            "DATA" => [$delete]
                                        ]
                                    ]
                                ]
                            ],
                        ],
                        $checkAll
                    ],
                ]
            ],
        ];
    }
    
    /**
     * Process data before send to grid
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected function processingQueryResults(): array
    {
        $list = [];
        $res = LinkedTaskFieldsTable::getList(
            [
                "select" => [
                    "FIELD_NAME", "FIELD_ID" => "FIELD.ID",
                    "LABEL" => "LANG.EDIT_FORM_LABEL",
                    "ACTIVE", "REQUIRED",
                    "DATE_CREATE", "CREATED_BY",
                    "TIMESTAMP_X", "MODIFIED_BY",
                    "CREATED_BY_USER_PHOTO" => "CREATED_BY_USER.PERSONAL_PHOTO",
                    "MODIFIED_BY_USER_PHOTO" => "MODIFIED_BY_USER.PERSONAL_PHOTO"
                ],
                "filter" => $this->filter,
                "count_total" => true,
                "offset" => $this->nav->getOffset(),
                "limit" => $this->nav->getLimit(),
                "order" => $this->sort["sort"],
                "count_total" => true,
                "cache" => [
                    "ttl" => 36000,
                    "cache_joins" => true
                ]
            ]
        );
        $result = $res->fetchAll();
        $this->arResult["TOTAL_ROWS_COUNT"] = $res->getCount();
        $this->nav->setRecordCount($this->arResult["TOTAL_ROWS_COUNT"]);

        if ($result) {
            $arUsersId = array_unique(array_merge(array_column($result, "CREATED_BY"), array_column($result, "MODIFIED_BY")));
            $users = User::getUserFormated($arUsersId);

            foreach ($result as $item) {
                $fieldName = $item["FIELD_NAME"];
                $lang = !$item["FIELD_ID"] ? Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_FIELD_DELETE", ["#FIELD#" => $fieldName]) : (!empty($item["LABEL"]) ? $item["LABEL"] : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_FIELD_NO_NAME", ["#FIELD#" => $fieldName]));
                $textAct = ($item["ACTIVE"] === "Y") ? Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_DEACT") : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_ACT");
                $newActive = ($item["ACTIVE"] === "Y") ? "N" : "Y";
                $label = !empty($item["LABEL"]) ? $item["LABEL"] : $item["FIELD_NAME"];
                $actions = [];

                if ($item["FIELD_ID"]) {
                    $actions = [
                        [
                            "text" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_EDIT"),
                            "onclick" => "BrixLinkedList.openSlider('$fieldName');"
                        ],
                        [
                            "text" => $textAct,
                            "onclick" => 'BrixLinkedList.update("' . $fieldName . '", "' . $newActive . '");',
                        ]
                    ];
                }

                $actions[] = [
                    "text" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_ACTION_DELETE"),
                    "onclick" => 'BrixLinkedList.delete("' . $fieldName . '", "' . $label . '");',
                ];

                $createdBy = $item["CREATED_BY"];
                $createdByName = "";
                $createdByStyle = "";
                $modifiedBy = $item["MODIFIED_BY"];
                $modifiedByName = "";
                $modifiedByStyle = "";

                if (array_key_exists($createdBy, $users)) {
                    $createdByName = !empty($users[$createdBy]) ? $users[$createdBy] : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_USER_ID_NO_NAME", ["#ID#" => $createdBy]);
                }
                if (array_key_exists($modifiedBy, $users)) {
                    $modifiedByName = !empty($users[$modifiedBy]) ? $users[$modifiedBy] : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_USER_ID_NO_NAME", ["#ID#" => $modifiedBy]);
                }

                if (!empty($item["CREATED_BY_USER_PHOTO"])) {
                    $createdByPhoto = str_starts_with($item["CREATED_BY_USER_PHOTO"], "http") ? $item["CREATED_BY_USER_PHOTO"] : \CFile::GetPath($item["CREATED_BY_USER_PHOTO"]);
                    $createdByStyle = " --ui-icon-service-bg-image: url('{$createdByPhoto}');";
                    $createdByStyle = 'style="' . $createdByStyle . '"';
                }
                if (!empty($item["MODIFIED_BY_USER_PHOTO"])) {
                    $modifiedByPhoto = str_starts_with($item["MODIFIED_BY_USER_PHOTO"], "http") ? $item["MODIFIED_BY_USER_PHOTO"] : \CFile::GetPath($item["MODIFIED_BY_USER_PHOTO"]);
                    $modifiedByStyle = " --ui-icon-service-bg-image: url('{$modifiedByPhoto}');";
                    $modifiedByStyle = 'style="' . $modifiedByStyle . '"';
                }

                $list[] = [
                    "id" => $fieldName,
                    "columns" => [
                        "FIELD_NAME" => $lang,
                        "ACTIVE" => ($item["ACTIVE"] === "Y") ? Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_CHECK_YES") : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_CHECK_NO"),
                        "REQUIRED" => ($item["REQUIRED"] === "Y") ? Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_CHECK_YES") : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_CHECK_NO"),
                        "DATE_CREATE" => $item["DATE_CREATE"],
                        "CREATED_BY" => !empty($createdByName) ? "<a class='brix-linked-list ui-icon-common-user' href='/company/personal/user/{$createdBy}/' {$createdByStyle}>{$createdByName}</a>" : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_USER_ID_DELETE", ["#ID#" => $createdBy]),
                        "TIMESTAMP_X" => $item["TIMESTAMP_X"],
                        "MODIFIED_BY" => !empty($modifiedByName) ? "<a class='brix-linked-list ui-icon-common-user' href='/company/personal/user/{$modifiedBy}/' {$modifiedByStyle}>{$modifiedByName}</a>" : Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_USER_ID_DELETE", ["#ID#" => $modifiedBy])
                    ],
                    "actions" => $actions,
                    "default_action" => $actions[0],
                ];
            }
        }
        
        return $list;
    }

    /**
     * Adds a filter
     * 
     * @return void
     */
    protected function addFilter(): void
    {
        Toolbar::addFilter([
            "GRID_ID" => $this->arResult["GRID_ID"],
            "FILTER_ID" => $this->arResult["FILTER_ID"],
            "FILTER" => $this->arResult["FILTER"],
            "RENDER_INTO_VIEW" => isset($this->arParams["~RENDER_FILTER_INTO_VIEW"]) ? $this->arParams["~RENDER_FILTER_INTO_VIEW"] : "",
            "NAVIGATION_BAR" => isset($this->arParams["~NAVIGATION_BAR"]) ? $this->arParams["~NAVIGATION_BAR"] : null,
            "ENABLE_LABEL" => true,
            "ENABLE_LIVE_SEARCH" => true,
        ]);
    }

    /**
     * Adds a buttons
     * 
     * @return void
     */
    protected function addButton(): void
    {
        $buttons = [
            new Button([
                "color" => Color::PRIMARY,
                "onclick" => new JsCode("BrixLinkedList.openSlider();"),
                "text" => Loc::getMessage("BRIX_LINKEDTASKFIELDS_LINKED_LIST_COMPONENT_GRID_BTN_ADD")
            ]),
            new Button([
                "color" => Color::LIGHT_BORDER,
                "icon" => Icon::INFO,
                "onclick" => new JsCode("BX.SidePanel.Instance.open('" . $this->getPath() . "/templates/.default/info.php', {cacheable: false, width: 1333});")
            ])
        ];

        foreach ($buttons as $button) {
            Toolbar::addButton($button);
        }
    }

    /**
     * Method for error output and delete favorite star
     * 
     * @return bool
     */
    private function isErrors(): bool
    {
        if (!$this->errors->isEmpty()) {
            foreach ($this->errors as $error) {
                ShowError($error->getMessage(), "ui-alert ui-alert-danger ui-alert-icon-warning ui-alert-inline");
            }

            return true;
        }

        return false;
    }
}