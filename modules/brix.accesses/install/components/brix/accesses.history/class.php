<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Error, ErrorCollection, Loader};
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Filter\Options as FilterOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Brix\Helpers\{Access, User};
use Brix\Tables\HistoryTable;

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

class BrixAccessesHistoryComponent extends CBitrixComponent
{
    /**
     * @var string
     */
    const MODULE_ID = "brix.accesses";
    public $id = "brix_accesses_history";
    public $filterId = "brix_accesses_history_filter";

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
        $this->errors = new ErrorCollection();
        $APPLICATION->SetTitle(Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_TITLE"));

        if (!Loader::IncludeModule(self::MODULE_ID)) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_ERROR_NOT_MODULE")));
        };
        
        if (!Loader::IncludeModule("iblock")) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_ERROR_NOT_MODULE_IBLOCK")));
        };

        if (!$this->isAccess()) {
            $this->errors->setError(new Error(Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_ERROR_ACCESS")));
        }

        if (!$this->isErrors()) {
            $this->prepareParams();
            $this->initVariables();
            $this->initFilter();
            $this->getGridFilter();
            $this->checkGridFilterData();
            $this->getPageSizes();
            $this->getGridColumns();

            $this->arResult["GRID_ID"] = $this->id;
            $this->arResult["NAV_OBJECT"] = $this->nav;
            $this->arResult["COLUMNS"] = $this->columns;
            $this->arResult["GRID_ROWS"] = $this->processingQueryResults();

            $this->includeComponentTemplate();
        }
    }

    /**
     * Checking access to the page
     * 
     * @return bool
     */
    private function isAccess()
    {
        $this->arResult["VIEW"] = Access::getAccess("history");

        return $this->arResult["VIEW"];
    }

    /**
     * Processing incoming parameters
     * 
     * @return void
     **/
    protected function prepareParams(): void
    {
        $this->arResult["SEF_FOLDER"] = $this->arParams["SEF_FOLDER"];
        $this->arResult["URL_DEFAULT"] = $this->arParams["URL_DEFAULT"];
        $this->arResult["URL_DETAIL"] = $this->arParams["URL_DETAIL"];
    }

    /**
     * Processing incoming parameters
     * 
     * @return void
     **/
    protected function initVariables(): void
    {
        $this->gridOptions = new GridOptions($this->id);
        $this->sort = $this->gridOptions->GetSorting(["sort" => ["ID" => "DESC"], "vars" => ["by" => "by", "order" => "order"]]);
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
        $this->arResult["FILTER"] = [
            [
                "id" => "ID",
                "name" => "ID",
                "type" => "number",
                "default" => true
            ],
            [
                "id" => "DATE_MODIFIED",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_DATE_MODIFIED"),
                "type" => "date",
                "default" => true
            ],
            [
                "id" => "USER_ID",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_USER_ID"),
                "type" => "dest_selector",
                "params" => [
                    "multiple" => "Y"
                ],
                "default" => true
            ],
            [
                "id" => "IBLOCK_ID",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_IBLOCK_ID"),
                "type" => "entity_selector",
                "params" => [
                    "multiple" => "Y",
                    "dialogOptions" => [
                        "context" => $this->filterId,
                        "hideOnSelect" => "N",
                        "entities" => [
                            [
                                "id" => "brix_iblocks"
                            ]
                        ]
                    ]
                ],
                "default" => true
            ],
            [
                "id" => "TYPE",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_TYPE"),
                "type" => "list",
                "items" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_TYPE_COLUMN"),
                "params" => [
                    "multiple" => "Y"
                ],
                "default" => true
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
                    case "ID_numsel":
                        switch ($value) {
                            case "exact":
                                $this->filter["=ID"] = $this->filterData["ID_from"];
                                break;
                            case "range":
                                if (!empty($this->filterData["ID_from"])) {
                                    $this->filter[">=ID"] = $this->filterData["ID_from"];
                                }

                                if (!empty($this->filterData["ID_to"])) {
                                    $this->filter["<=ID"] = $this->filterData["ID_to"];
                                }
                                break;
                            case "more":
                                if (!empty($this->filterData["ID_from"])) {
                                    $this->filter[">ID"] = $this->filterData["ID_from"];
                                }
                                break;
                            case "less":
                                if (!empty($this->filterData["ID_to"])) {
                                    $this->filter["<ID"] = $this->filterData["ID_to"];
                                }
                                break;
                            default:
                                break;
                        }
                        break;
                    case "DESCRIPTION":
                    case "FIND":
                        $this->filter["%DESCRIPTION"] = $value;
                        break;
                    case "TYPE":
                    case "IBLOCK_ID":
                        $this->filter[$key] = $value;
                        break;
                    case "USER_ID":
                        $this->filter["={$key}"] = array_map(function($v)
                        {
                            return str_replace("U", "", $v);
                        }, $value);
                        break;
                    case "DATE_MODIFIED_from":
                        $key = str_replace("_from", "", $key);
                        $this->filter[">={$key}"] = $value;
                        break;
                    case "DATE_MODIFIED_to":
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
                "id" => "ID",
                "sort" => "ID",
                "name" => "ID",
                "default" => true
            ],
            [
                "id" => "DATE_MODIFIED",
                "sort" => "DATE_MODIFIED",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_DATE_MODIFIED"),
                "default" => true
            ],
            [
                "id" => "USER_ID",
                "sort" => "USER_ID",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_USER_ID"),
                "default" => true
            ],
            [
                "id" => "IBLOCK_ID",
                "sort" => "IBLOCK_ID",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_IBLOCK_ID"),
                "default" => true
            ],
            [
                "id" => "TYPE",
                "sort" => "TYPE",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_TYPE"),
                "default" => true
            ],
            [
                "id" => "DESCRIPTION",
                "name" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_DESCRIPTION"),
                "default" => true
            ]
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
        $dateTimeFormat = DateTime::getFormat();
        $dateTimeFormat = str_replace([":SS", ":ss", ":s"], ["", "", ""], $dateTimeFormat);
        $res = HistoryTable::getList(
            [
                "select" => ["*", "USER_PHOTO" => "USER.PERSONAL_PHOTO", "IBLOCK_NAME" => "IBLOCK.NAME"],
                "filter" => $this->filter,
                "count_total" => true,
                "offset" => $this->nav->getOffset(),
                "limit" => $this->nav->getLimit(),
                "order" => $this->sort["sort"],
                "count_total" => true,
                "cache" => [
                    "ttl" => 3600,
                    "cache_joins" => true
                ]
            ]
        );
        $result = $res->fetchAll();
        $this->nav->setRecordCount($res->getCount());

        if ($result) {
            $arUsersId = array_unique(array_column($result, "USER_ID"));
            $users = User::getUserFormated($arUsersId);

            foreach ($result as $item) {
                $detailUrl = $this->arResult["SEF_FOLDER"] . str_replace("#ID#", $item["ID"], $this->arResult["URL_DETAIL"]);
                $actions = [
                    [
                        "text" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_ACTION_OPEN"),
                        "onclick" => "BX.SidePanel.Instance.open('{$detailUrl}', {cacheable: true, width: 725});"
                    ]
                ];
                $userId = $item["USER_ID"];
                $userName = "";
                $style = "";

                if (array_key_exists($userId, $users)) {
                    $userName = !empty($users[$userId]) ? $users[$userId] : Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_USER_ID_NO_NAME", ["#ID#" => $userId]);
                }

                if (!empty($item["USER_PHOTO"])) {
                    $photo = str_starts_with($item["USER_PHOTO"], "http") ? $item["USER_PHOTO"] : \CFile::GetPath($item["USER_PHOTO"]);
                    $style = " --ui-icon-service-bg-image: url('{$photo}');";
                    $style = 'style="' . $style . '"';
                }

                $list[] = [
                    "data" => [
                        "ID" => $item["ID"],
                        "DATE_MODIFIED" => $item["DATE_MODIFIED"] ? $item["DATE_MODIFIED"]->format($dateTimeFormat) : "",
                        "USER_ID" => !empty($userName) ? "<a class='brix-history-link ui-icon-common-user' href='/company/personal/user/{$userId}/' {$style}>{$userName}</a>" : Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_USER_ID_DELETE", ["#ID#" => $userId]),
                        "IBLOCK_ID" => $item["IBLOCK_NAME"] ? $item["IBLOCK_NAME"] . " [" . $item["IBLOCK_ID"] . "]" : Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_IBLOCK_ID_DELETE", ["#ID#" => $item["IBLOCK_ID"]]),
                        "TYPE" => Loc::getMessage("BRIX_ACCESSES_HISTORY_COMPONENT_GRID_TYPE_COLUMN")[$item["TYPE"]],
                        "DESCRIPTION" => $item["DESCRIPTION"]
                    ],
                    "actions" => $actions,
                    "default_action" => $actions[0],
                ];
            }
        }
        
        return $list;
    }
    
    /**
     * Method for error output and delete favorite star
     * 
     * @return bool
     */
    private function isErrors(): bool
    {
        if (!$this->errors->isEmpty()) {
            Toolbar::deleteFavoriteStar();

            foreach ($this->errors as $error) {
                ShowError($error->getMessage(), "ui-alert ui-alert-danger ui-alert-icon-warning ui-alert-inline");
            }

            return true;
        }

        return false;
    }
}