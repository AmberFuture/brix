<?php
namespace Brix\Api\Providers;

use Bitrix\Main\{ArgumentException, Loader, LoaderException, ObjectPropertyException, SystemException};
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Socialnetwork\WorkgroupTable;
use Bitrix\Tasks\Internals\Task\LabelTable;
use Bitrix\UI\EntitySelector\{BaseProvider, Dialog, Item, SearchQuery, Tab};

/**
 * Class TaskTagProvider
 *
 * @package Brix\Api\Providers
 */
class TaskTagProvider extends BaseProvider
{
    /**
     * @var string
     */
    const ENTITY_ID = "brix_task_tag";
    
    /**
     * @var int
     */
    private $limit = 100;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct();

        if (array_key_exists("limit", $options) && $options["limit"] && is_numeric($options["limit"])) {
            $this->limit = (int) $options["limit"];
        }
    }

    /**
     * Checking: is available for use
     *
     * @return bool
     * @throws LoaderException
     */
    public function isAvailable(): bool
    {
        if (!CurrentUser::get()->getId() || !Loader::includeModule("tasks") || !Loader::includeModule("socialnetwork")) {
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
        return $this->getTags($ids);
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
        return $this->getTags($ids);
    }

    /**
     * Returns a list of information type blocks
     * 
     * @param array $ids
     * @param array $options
     * @param bool $returnFetchAll
     * @return array
     */
    public function getTags(array $ids = [], array $options = [], bool $returnFetchAll = false): array
    {
        $items = [];
        $select = ["ID", "NAME", "GROUP_NAME" => "GROUP.NAME"];
        $order = ["NAME" => "ASC"];
        $filter = array_key_exists("filter", $options) ? $options["filter"] : [];
        $limit = (array_key_exists("limit", $options) && (int) $options["limit"] > 0) ? $options["limit"] : self::getLimit();

        if ($ids) {
            $filter["ID"] = array_unique($ids);
        }

        if (array_key_exists("searchQuery", $options) && !empty($options["searchQuery"]) && is_string($options["searchQuery"])) {
            $filter["?NAME"] = $options["searchQuery"];
        }

        $labels = LabelTable::getList([
            "select" => $select,
            "order" => $order,
            "filter" => $filter,
            "limit" => $limit,
            "runtime" => [
                new Reference(
                    "GROUP",
                    WorkgroupTable::class,
                    Join::on("this.GROUP_ID", "ref.ID")
                )
            ]
        ])->fetchAll();

        if ($labels) {
            $items = $returnFetchAll ? array_map([$this, "createItem"], $labels) : $labels;
        }

        return $items;
    }

    /**
     * Creates an Item
     * 
     * @param array $label
     * @return Item
     */
    private function createItem(array $label): Item
    {
        $title = $label["GROUP_NAME"] ? $label["NAME"] . " [" . $label["GROUP_NAME"] . "]" : $label["NAME"];
        $item = [
            "id" => $label["ID"],
            "entityId" => self::ENTITY_ID,
            "title" => $title,
            "entityType" => "default",
            "tabs" => self::ENTITY_ID,
            "availableInRecentTab" => true,
            "searchable" => true
        ];

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
        $opt = [
            "limit" => $limit
        ];


        $arLabels = $this->getTags([], $opt, true);
        $limitExceeded = $limit <= count($arLabels);

        if (!$limitExceeded) {
            $entity = $dialog->getEntity(self::ENTITY_ID);

            if ($entity) {
                $entity->setDynamicSearch(false);
            }
        }

        $forceDynamic = !$limitExceeded ? false : null;
        $this->fillTree($dialog, $arLabels, $forceDynamic);

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
        return Loc::getMessage("BRIX_LINKEDTASKFIELDS_TASKTAGPROVIEDER_TITLE");
    }

    /**
     * Fill dialog by tags tree
     *
     * @param Dialog $dialog
     * @param array $arLabels
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function fillTree(Dialog $dialog, array $arLabels)
    {
        $dialog->addItems($arLabels);
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

        $arTags = $this->getTags([], $filter, $limit);

        if ($limit <= count($arList)) {
            $searchQuery->setCacheable(false);
        }

        if ($arTags) {
            foreach ($arTags as $arTag) {
                $dialog->addItem(new Item([
                    "id" => $arTag["ID"],
                    "entityId" => self::ENTITY_ID,
                    "title" => $arTag["NAME"],
                    "entityType" => "default"
                ]));
            }
        }
    }
}
