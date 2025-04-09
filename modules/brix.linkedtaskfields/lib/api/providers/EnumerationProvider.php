<?php
namespace Brix\Api\Providers;

use Bitrix\Main\{ArgumentException, Loader, LoaderException, ObjectPropertyException, SystemException};
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\EntitySelector\{BaseProvider, Dialog, Item, SearchQuery, Tab};
use Brix\Helpers\UserFields;

/**
 * Class EnumerationProvider
 *
 * @package Brix\Api\Providers
 */
class EnumerationProvider extends BaseProvider
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.linkedtaskfields";
    const ENTITY_ID = "brix_enumeration";
    
    /**
     * @var int
     */
    private $id = 0;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct();

        if (array_key_exists("id", $options) && $options["id"] && is_numeric($options["id"])) {
            $this->id = (int) $options["id"];
        } else {
            return false;
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
        if (!CurrentUser::get()->getId() || !Loader::includeModule(self::MODULE_ID)) {
            return false;
        }

        return true;
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
        return $this->getEnums($ids);
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
        return $this->getEnums($ids);
    }

    /**
     * Returns a list of information type blocks
     * 
     * @param array $ids
     * @param array $options
     * @param bool $returnFetchAll
     * @return array
     */
    public function getEnums(array $ids = [], array $options = [], bool $returnFetchAll = false): array
    {
        $items = [];
        $filter = array_key_exists("filter", $options) ? $options["filter"] : [];

        if ($ids) {
            $filter["ID"] = array_unique($ids);
        }

        if ($this->id) {
            $filter["USER_FIELD_ID"] = $this->id;
        }

        if (array_key_exists("searchQuery", $options) && !empty($options["searchQuery"]) && is_string($options["searchQuery"])) {
            $filter["?VALUE"] = $options["searchQuery"];
        }

        $arEnums = UserFields::getEnumerations($filter);

        if ($arEnums) {
            $items = $returnFetchAll ? array_map([$this, "createItem"], $arEnums) : $arEnums;
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
        $item = [
            "id" => $label["ID"],
            "entityId" => self::ENTITY_ID,
            "title" => $label["VALUE"],
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
        $arEnums = $this->getEnums([], [], true);
        $entity = $dialog->getEntity(self::ENTITY_ID);

        if ($entity) {
            $entity->setDynamicSearch(false);
        }

        $this->fillTree($dialog, $arEnums);

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
        return Loc::getMessage("BRIX_LINKEDTASKFIELDS_ENUMERATIONPROVIEDER_TITLE");
    }

    /**
     * Fill dialog by enumerations tree
     *
     * @param Dialog $dialog
     * @param array $arEnums
     * @return void
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    private function fillTree(Dialog $dialog, array $arEnums)
    {
        $dialog->addItems($arEnums);
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
        $query = $searchQuery->getQuery();
        $arList = [];

        if (!empty($query) && is_string($query)) {
            $filter = ["?VALUE" => $query];
        }

        $arEnums = $this->getEnums([], $filter);

        $searchQuery->setCacheable(false);

        if ($arEnums) {
            foreach ($arEnums as $enum) {
                $dialog->addItem(new Item([
                    "id" => $enum["ID"],
                    "entityId" => self::ENTITY_ID,
                    "title" => $enum["VALUE"],
                    "entityType" => "default"
                ]));
            }
        }
    }
}
