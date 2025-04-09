<?php
namespace Brix\Api\Providers;

use Bitrix\Iblock\{IblockTable, TypeLanguageTable, TypeTable};
use Bitrix\Main\{ArgumentException, Loader, ObjectPropertyException, SystemException};
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\UI\EntitySelector\{BaseProvider, Dialog, Item, SearchQuery, Tab};

/**
* Class IblockProvider
* 
* @package Brix\Api\Providers
*/
class IblockProvider extends BaseProvider
{
    /**
     * @var string
     */
    public const MODULE_ID = "brix.accesses";
    public const ENTITY_ID = "brix_iblocks";
    public const MODE_ALL = "all";
    public const MODE_TYPES = "types";
    public const MODE_IBLOCKS = "iblocks";
    
    /**
     * @var int
     */
    private $limit = 300;
    
    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct();
        
        if (
            array_key_exists("mode", $options) && !empty($options["mode"]) &&
            is_string($options["mode"]) && in_array($options["mode"], self::getSelectModes())
        ) {
            $this->options["mode"] = $options["mode"];
        } else {
            $this->options["mode"] = self::MODE_ALL;
        }

        if (array_key_exists("limit", $options) && $options["limit"] && is_numeric($options["limit"])) {
            $this->limit = (int) $options["limit"];
        }

        $this->options["rights"] = (array_key_exists("rights", $options) && $options["rights"]) ? $options["rights"] : false;
    }
    
    /**
     * Get available type mode list
     *
     * @return string[]
     */
    public static function getSelectModes(): array
    {
        return [
            self::MODE_ALL,
            self::MODE_TYPES,
            self::MODE_IBLOCKS
        ];
    }
    
    /**
     * Get type mode
     *
     * @return mixed
     */
    public function getSelectMode(): mixed
    {
        return $this->options["mode"];
    }

    /**
     * Checking: is available for use
     *
     * @return bool
     * @throws LoaderException
     */
    public function isAvailable(): bool
    {
        if (!CurrentUser::get()->getId() || !Loader::includeModule("iblock")) {
            return false;
        }

        return true;
    }

    /**
     * Get available limit for query
     *
     * @return int
     */
    public function getLimit(): int
    {
        return (int) $this->limit;
    }

    /**
     * Get items for dialog entity
     *
     * @param array $ids
     * @return array|Item[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getItems(array $ids): array
    {
        return $this->getTypesIblock($ids);
    }

    /**
     * Get selected items for dialog entity
     *
     * @param array $ids
     * @return array|Item[]
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getSelectedItems(array $ids): array
    {
        return $this->getTypesIblock($ids);
    }

    /**
     * Returns a list of information type blocks
     * 
     * @param array $ids
     * @param array $options
     * @param bool $returnFetchAll
     * @return array
     */
    public function getTypesIblock(array $ids = [], array $options = [], bool $returnFetchAll = false): array
    {
        $items = [];
        $lang = defined(LANG) ? LANG : "ru";
        $select = ["ID", "NAME" => "LANGS.NAME"];
        $order = ["SORT" => "ASC"];
        $filter = array_key_exists("filter", $options) ? $options["filter"] : [];
        $filter["LANGS.LANGUAGE_ID"] = $lang;

        if (array_key_exists("select", $options) && $options["select"]) {
            $select = array_merge($select, $options["select"]);
        }

        if ($ids) {
            $filter["ID"] = array_unique($ids);
        }

        if (array_key_exists("searchQuery", $options) && !empty($options["searchQuery"]) && is_string($options["searchQuery"])) {
            $filter["?NAME"] = $options["searchQuery"];
        }

        $runtime = array_key_exists("runtime", $options) ? $options["runtime"] : [];
        $runtime[] = new Reference(
            "LANGS",
            TypeLanguageTable::class,
            Join::on("this.ID", "ref.IBLOCK_TYPE_ID")
        );
        $limit = (array_key_exists("limit", $options) && (int) $options["limit"] > 0) ? $options["limit"] : self::getLimit();
        $types = TypeTable::getList([
            "select" => $select,
            "order" => $order,
            "filter" => $filter,
            "runtime" => $runtime,
            "limit" => $limit
        ])->fetchAll();

        if (!$returnFetchAll) {
            if ($types) {
                $items = array_map([$this, "createItem"], $types);

                if ($this->getSelectMode() !== self::MODE_TYPES) {
                    foreach ($items as &$item) {
                        $item->setSearchable(false);
                        $item->setAvailableInRecentTab(false);
                        $item->setNodeOptions([
                            "title" => $item->getTitle(),
                            "avatar" => "/bitrix/images/" . self::MODULE_ID . "/ui.entity-selector/" . Loc::getMessage("BRIX_ACCESSES_IBLOCK_PROVIDER_ICON_TYPE_IBLOCK"),
                            "dynamic" => true
                        ]);
                    }
                }
            }
        } else {
            $items = $types;
        }

        return $items;
    }

    /**
     * Creates an Item
     * 
     * @param array $arIblock
     * @param bool $availableInRecentTab
     * @param bool $dynamic
     * @return Item
     */
    private function createItem(array $arIblock, bool $availableInRecentTab = true, bool $dynamic = false): Item
    {
        $entityType = (array_key_exists("ACTIVE", $arIblock) && $arIblock["ACTIVE"] === "N") ? "inactive" : "default";
        $avatar = array_key_exists("IBLOCK_TYPE_ID", $arIblock) ? "BRIX_ACCESSES_IBLOCK_PROVIDER_ICON_IBLOCK" : "BRIX_ACCESSES_IBLOCK_PROVIDER_ICON_TYPE_IBLOCK";
        $ext = str_replace(".", "-", self::MODULE_ID);
        $size = ["bgSize" => "24px 24px"];
        $item = [
            "id" => $arIblock["ID"],
            "entityId" => self::ENTITY_ID,
            "title" => $arIblock["NAME"],
            "entityType" => $entityType,
            "tabs" => self::ENTITY_ID,
            "availableInRecentTab" => $availableInRecentTab,
            "searchable" => $availableInRecentTab,
            "avatar" => "/bitrix/images/{$ext}/entity-selector/" . Loc::getMessage($avatar),
            "nodeOptions" => [
                "title" => $arIblock["NAME"],
                "avatar" => "/bitrix/images/{$ext}/entity-selector/" . Loc::getMessage($avatar),
                "dynamic" => $dynamic
            ]
        ];

        if (array_key_exists("IBLOCK_TYPE_ID", $arIblock)) {
            $item["customData"] = [
                "rights" => $arIblock["RIGHTS_MODE"]
            ];
            $item["nodeOptions"]["avatarOptions"] = $size;
            $item["avatarOptions"] = $size;
        }

        return new Item($item);
    }

    /**
     * Add items to dialog entity
     *
     * @param Dialog $dialog
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function fillDialog(Dialog $dialog): void
    {
        $limit = $this->getLimit();
        $filter = [">=COUNT_IBLOCK" => 1];
        $select = [];

        if ($this->options["rights"]) {
            $filter["IBLOCKS_MODE"] = $this->options["rights"];
            $select = ["IBLOCKS_MODE" => "IBLOCKS.RIGHTS_MODE"];
        }

        $opt = [
            "select" => $select,
            "filter" => $filter,
            "runtime" => [
                new Reference(
                    "IBLOCKS",
                    IblockTable::class,
                    Join::on("this.ID", "ref.IBLOCK_TYPE_ID")
                ),
                "COUNT_IBLOCK" => [
                    "data_type" => "integer",
                    "expression" => ["count(%s)", "IBLOCKS.ID"]
                ]
            ],
            "limit" => $limit
        ];

        if ($this->getSelectMode() === self::MODE_TYPES) {
            $opt = [];
        }

        $arTypes = $this->getTypesIblock([], $opt, true);
        $limitExceeded = $limit <= count($arTypes);

        if (!$limitExceeded || $this->getSelectMode() === self::MODE_IBLOCKS) {
            $entity = $dialog->getEntity(self::ENTITY_ID);

            if ($entity) {
                $entity->setDynamicSearch(false);
            }
        }

        $forceDynamic = ($this->getSelectMode() === self::MODE_TYPES || !$limitExceeded) ? false : null;
        $this->fillTree($dialog, $arTypes, $forceDynamic);

        $dialog->addTab(new Tab([
            "id" => self::ENTITY_ID,
            "title" => $this->getTabTitle()
        ]));
    }
    
    /**
     * Get title for tab
     *
     * @return string|void|null
     */
    private function getTabTitle()
    {
        switch ($this->getSelectMode()) {
            case self::MODE_ALL:
            case self::MODE_IBLOCKS:
                return Loc::getMessage("BRIX_ACCESSES_IBLOCK_PROVIDER_TAB_TITLE_IBLOCK");
            case self::MODE_TYPES:
                return Loc::getMessage("BRIX_ACCESSES_IBLOCK_PROVIDER_TAB_TITLE_TYPE");
        }
    }

    /**
     * Fill dialog by iblock tree
     *
     * @param Dialog $dialog
     * @param array $arTypes
     * @param bool|null $forceDynamic
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function fillTree(Dialog $dialog, array $arTypes, ?bool $forceDynamic = null)
    {
        $parents = [];
        $limit = ($this->getSelectMode() !== self::MODE_TYPES) ? $this->getLimit() : null;
        $arIblocks = $this->getIblocks([], [], $limit);
        $dynamic = is_bool($forceDynamic) ? $forceDynamic : true;
        $availableInRecentTab = $this->getSelectMode() === self::MODE_TYPES;

        if ($this->getSelectMode() !== self::MODE_IBLOCKS) {
            foreach ($arTypes as $arType) {
                $item = $this->createItem($arType, $availableInRecentTab, $dynamic);

                if ($this->getSelectMode() !== self::MODE_TYPES) {
                    foreach ($arIblocks as $arIblock) {
                        if ($arIblock["IBLOCK_TYPE_ID"] === (string) $item->getId()) {
                            $childItem = $this->createItem($arIblock, true);
                            $item->addChild($childItem);
                        }
                    }
                }

                $parentItem = $parents[$arType["ID"]] ?? null;

                if ($parentItem) {
                    $parentItem->addChild($item);
                } else {
                    $dialog->addItem($item);
                }
                
                $parents[$arType["ID"]] = $item;
            }
        } else {
            foreach ($arIblocks as $arIblock) {
                $item = $this->createItem($arIblock);
                $dialog->addItem($item);
            }
        }
    }

    /**
     * Get iblocks
     * 
     * @param array $ids
     * @param array $additionalFilter
     * @param ?int $limit
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getIblocks(array $ids = [], array $additionalFilter = [], ?int $limit = null): array
    {
        $order = ["SORT" => "ASC"];
        $select = ["ID", "NAME", "ACTIVE", "IBLOCK_TYPE_ID", "RIGHTS_MODE"];
        $filter = !empty($additionalFilter) ? $additionalFilter : [];

        if ($ids) {
            $filter["ID"] = array_unique(array_map("intval", $ids));
        }

        if ($this->options["rights"]) {
            $filter["RIGHTS_MODE"] = $this->options["rights"];
        }

        $dbIblocks = IblockTable::getList([
            "order" => $order,
            "filter" => $filter,
            "select" => $select,
            "limit" => $limit
        ])->fetchAll();

        return $dbIblocks;
    }

    /**
     * Searching by text input
     *
     * @param SearchQuery $searchQuery
     * @param Dialog $dialog
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
    {
        $filter = [];
        $limit = $this->getLimit();
        $query = $searchQuery->getQuery();
        $arList = [];

        if (!empty($query) && is_string($query)) {
            $filter = ["?NAME" => $query];
        }

        if ($this->getSelectMode() === self::MODE_TYPES) {
            $arList = $this->getTypesIblock([], ["filter" => $filter, "limit" => $limit], true);
        } else {
            $arList = $this->getIblocks([], $filter, $limit);
        }

        if ($limit <= count($arList)) {
            $searchQuery->setCacheable(false);
        }

        if ($arList) {
            foreach ($arList as $arItem) {
                $entityType = (array_key_exists("ACTIVE", $arItem) && $arItem["ACTIVE"] === "N") ? "inactive" : "default";
                $customData = array_key_exists("RIGHTS_MODE", $arItem) ? ["rights" => $arItem["RIGHTS_MODE"]] : [];
                $dialog->addItem(new Item([
                    "id" => $arItem["ID"],
                    "entityId" => self::ENTITY_ID,
                    "title" => $arItem["NAME"],
                    "entityType" => $entityType,
                    "customData" => $customData
                ]));
            }
        }
    }
}