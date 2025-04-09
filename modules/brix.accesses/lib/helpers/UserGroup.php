<?php
namespace Brix\Helpers;

use Bitrix\Main\{GroupTable, UserGroupTable};
use Bitrix\Main\Engine\CurrentUser;

/**
 * Class UserGroup
 * 
 * @package Brix\Helpers
 */
class UserGroup
{
    /**
     * Getting a list of user groups
     * 
     * @param bool $admin
     * @param bool $active
     * @param array $order
     * @return array
     * [
     *  groupId => "Name [groupId]"
     * ]
     */
    public static function getGroupList(bool $admin = false, bool $active = true, array $order = []): array
    {
        $select = ["ID", "NAME"];
        $filter = !$admin ? ["!ID" => 1] : [];
        $filter = $active ? array_merge($filter, ["ACTIVE" => "Y"]) : $filter;
        $order = $order ?? ["NAME" => "ASC"];
        $dbList = GroupTable::getList([
            "order" => $order,
            "select" => $select,
            "filter" => $filter
        ])->fetchAll();
        $keys = array_column($dbList, "ID");
        $values = array_map(function($name, $id) {
            return "{$name} [{$id}]";
        }, array_column($dbList, "NAME"), $keys);
        $grList = $dbList ? array_combine($keys, $values) : [];

        return $grList;
    }

    /**
     * Returns a list of user groups
     * 
     * @param int $id
     * @return array
     */
    public static function getUserGroup(int $id = 0): array
    {
        $groups = [];

        if (!$id) {
            $id = CurrentUser::get()->getId();
        }

        if ($id) {
            $dbList = UserGroupTable::getList([
                "select" => ["GROUP_ID"],
                "filter" => ["USER_ID" => $id, "GROUP.ACTIVE" => "Y"]
            ])->fetchAll();
            $groups = $dbList ? array_column($dbList, "GROUP_ID") : [];
        }

        return $groups;
    }
}