<?php
namespace Brix\Api\Controllers;

use Bitrix\Main\Engine\{ActionFilter, Controller};
use Brix\Tables\LinkedTaskFieldsTable;

/**
* Class Linked
* 
* @package Brix\Api\Controllers
*/
final class Linked extends Controller
{
    /**
     * Configures ajax actions
     * 
     * @return array
     */
    public function configureActions(): array
    {
        $configuration = [];

        foreach (["update", "updateMulti", "delete"] as $action) {
            $configuration[$action] = [
                "prefilters" => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ])
                ],
                "postfilters" => []
            ];
        }

        return $configuration;
    }

    /**
     * Updates the record
     * 
     * @param array $fields
     * @param string $fieldName
     * @param bool $activity - change only the activity of an element
     * @return mixed
     */
    public static function updateAction(array $fields, string $fieldName = "", bool $activity = false): mixed
    {
        if (!$fields) {
            return ["error" => "There is no data to change"];
        }

        if (!empty($fieldName)) {
            $row = LinkedTaskFieldsTable::getByPrimary(
                $fieldName,
                [
                    "select" => ["FIELD_NAME"]
                ]
            )->fetch();

            if (!$row) {
                if ($activity) {
                    return ["error" => "Element not found. Refresh the page"];
                } else {
                    $fieldName = "";
                }
            }
        }

        if (!empty($fieldName)) {
            return LinkedTaskFieldsTable::update($fieldName, $fields);
        } else {
            return LinkedTaskFieldsTable::add($fields);
        }

        return [];
    }

    /**
     * Updates the record
     * 
     * @param array $fields
     * @param array $fieldNames
     * @param string $all
     * @return void
     */
    public static function updateMultiAction(array $fields, array $fieldNames = [], string $all = "N"): void
    {
        if ($all === "Y") {
            $db = LinkedTaskFieldsTable::getList([
                "select" => ["FIELD_NAME"]
            ])->fetchAll();
            $fieldNames = $db ? array_column($db, "FIELD_NAME") : [];
        }

        if ($fieldNames) {
            foreach ($fieldNames as $fieldName) {
                LinkedTaskFieldsTable::update($fieldName, $fields);
            }
        }
    }

    /**
     * Deletes an entry
     * 
     * @param array $fieldNames
     * @param string $all
     * @return void
     */
    public static function deleteAction(array $fieldNames = [], string $all = "N"): void
    {
        if ($all === "Y") {
            $db = LinkedTaskFieldsTable::getList([
                "select" => ["FIELD_NAME"]
            ])->fetchAll();
            $fieldNames = $db ? array_column($db, "FIELD_NAME") : [];
        }

        if ($fieldNames) {
            foreach ($fieldNames as $fieldName) {
                LinkedTaskFieldsTable::delete($fieldName);
            }
        }
    }
}