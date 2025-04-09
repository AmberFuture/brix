<?php
namespace Brix\Api\Controllers;

use Bitrix\Main\{Loader, ObjectException, UserFieldTable};
use Bitrix\Main\Engine\{ActionFilter, Controller, CurrentUser};
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\{Date, DateTime};
use Bitrix\Tasks\Internals\Task\TaskTagTable;
use Bitrix\Tasks\Manager\Task as ManagerTask;
use Brix\Helpers\{DefaultFields, User, UserFields};
use Brix\Tables\LinkedTaskFieldsTable;

/**
* Class Task
* 
* @package Brix\Api\Controllers
*/
final class Task extends Controller
{
    /**
     * Configures ajax actions
     * 
     * @return array
     */
    public function configureActions(): array
    {
        $configuration = [];

        foreach (["get", "checkConditions"] as $action) {
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
     * Getting information about an issue and rule settings
     * 
     * @param int $id
     * @param array $taskData
     * @return mixed
     */
    public static function getAction(int $id = 0, array $taskData = []): array
    {
        $arResult = [
            "CONDITIONS" => [],
            "RELATIONSHIP" => [],
            "DEFAULT_FIELDS" => [],
            "FIELDS" => [],
            "HIDE_FIELD" => [],
            "CURRENT_VALUES" => []
        ];
        $userId = CurrentUser::get()->getId();

        if ($userId && Loader::includeModule("tasks")) {
            $linkedConditions = LinkedTaskFieldsTable::getList([
                "select" => ["FIELD_NAME", "REQUIRED", "CONDITIONS", "FIELD_ID" => "FIELD.ID"],
                "filter" => ["ACTIVE" => "Y"],
                "runtime" => [
                    new Reference(
                        "FIELD",
                        UserFieldTable::class,
                        Join::on("this.FIELD_NAME", "ref.FIELD_NAME")
                    )
                ],
                "cache" => [
                    "ttl" => 86400
                ]
            ])->fetchAll();

            if ($linkedConditions) {
                $arUsField = [];

                foreach ($linkedConditions as $linked) {
                    $arUsField = array_merge($arUsField, array_column($linked["CONDITIONS"], "field"));
                }

                $arUsField = array_unique($arUsField);
                $defaultFields = DefaultFields::get();
                $defaultNames = array_values(array_intersect($arUsField, array_keys($defaultFields)));
                $arResult["DEFAULT_FIELDS"] = $defaultNames ?? [];
                $defaultFields = array_intersect_key($defaultFields, array_flip(array_intersect(array_keys($defaultFields), $defaultNames)));
                $arUsField = array_values(array_diff($arUsField, $defaultNames));
                $usFields = $arUsField ? array_merge($defaultFields, UserFields::getListFields(true, ["FIELD_NAME" => $arUsField])) : $defaultFields;
                $arResult["FIELDS"] = array_combine(array_column($usFields, "FIELD_NAME"), $usFields);

                foreach ($linkedConditions as $linked) {
                    $conditions = [];
                    $temp = [];
                    $length = (int) count($linked["CONDITIONS"]) - 1;

                    foreach ($linked["CONDITIONS"] as $key => $cond) {
                        if ($key % 2 === 0) {
                            $temp[] = $cond;

                            if (
                                !array_key_exists($cond["field"], $arResult["RELATIONSHIP"]) ||
                                !in_array($linked["FIELD_NAME"], $arResult["RELATIONSHIP"][$cond["field"]])
                            ) {
                                $arResult["RELATIONSHIP"][$cond["field"]][] = $linked["FIELD_NAME"];
                            }

                            if ((int) $key === $length) {
                                $conditions[] = $temp;
                            }
                        } else {
                            if ($cond === "or") {
                                $conditions[] = $temp;
                                $temp = [];
                            }
                        }
                    }

                    $arResult["CONDITIONS"][$linked["FIELD_NAME"]] = [
                        "REQUIRED" => $linked["REQUIRED"],
                        "FIELD_ID" => $linked["FIELD_ID"],
                        "CONDITIONS" => $conditions
                    ];
                }

                $arResult["HIDE_FIELD"] = array_keys($arResult["CONDITIONS"]);

                if ($id !== 0) {
                    $task = ManagerTask::get($userId, $id, ["PUBLIC_MODE" => true]);
                    self::checkTask($id, $arResult["FIELDS"], $task["DATA"], $arResult["CURRENT_VALUES"]);
                } elseif (count($arResult["FIELDS"]) > 0) {
                    foreach ($arResult["FIELDS"] as $field => $val) {
                        if ($field === "CREATED_BY" || $field === "RESPONSIBLE_ID") {
                            $arResult["CURRENT_VALUES"][$field] = "U" . $userId;
                        } else {
                            $arResult["CURRENT_VALUES"][$field] = null;
                        }
                    }
                }

                if (count($taskData) > 0) {
                    self::checkTask($id, $arResult["FIELDS"], $taskData, $arResult["CURRENT_VALUES"]);
                }

                foreach ($arResult["CONDITIONS"] as $field => $arConditions) {
                    $completed = self::checkConditionsAction($arConditions["CONDITIONS"], $arResult["CURRENT_VALUES"], $arResult["FIELDS"]);

                    if ($completed) {
                        $ind = array_search($field, $arResult["HIDE_FIELD"]);
                        unset($arResult["HIDE_FIELD"][$ind]);
                    }
                }
            }
        }

        return $arResult;
    }

    /**
     * Checks whether the field's display condition is met
     * 
     * @param array $arConditions
     * @param array $arCurrentValues
     * @param array $arFields
     * @return bool
     */
    public static function checkConditionsAction(array $arConditions, array $arCurrentValues, array $arFields): bool
    {
        $completed = false;

        for ($i = 0; $i < count($arConditions); $i++) {
            for ($j = 0; $j < count($arConditions[$i]); $j++) {
                if ($j > 0 && !$completed) {
                    break;
                }

                $fieldName = $arConditions[$i][$j]["field"];
                $fieldType = $arFields[$fieldName]["USER_TYPE_ID"];
                $multiple = ($arFields[$fieldName]["MULTIPLE"] === "Y");
                $typeCondition = $arConditions[$i][$j]["type"];
                $currentValues = array_key_exists($fieldName, $arCurrentValues) ? $arCurrentValues[$fieldName] : false;
                $value = $arConditions[$i][$j]["value"];

                switch ($typeCondition) {
                    case "FILL":
                        if ($fieldName === "GROUP_ID") {
                            $completed = ($currentValues && (int) $currentValues !== 0);
                        } else {
                            $completed = $currentValues ? true : false;
                        }
                        break;
                    case "IN":
                    case "IN_NO":
                        if ($currentValues && !$multiple) {
                            if ($fieldType === "employee") {
                                $completed = in_array($currentValues, User::getUserDepartments($value[0]));
                            } elseif (
                                $fieldType === "group" || $fieldType === "iblock_section" ||
                                $fieldType === "iblock_element" || $fieldType === "enumeration"
                            ) {
                                $valInteger = array_map("intval", $value[0]);
                                $completed = in_array(intval($currentValues), $valInteger);
                            } elseif ($fieldType === "crm") {
                                $convertValues = self::crmConvert($currentValues);
                                $completed = in_array($convertValues, $value[0]);
                            } elseif ($fieldType === "string") {
                                $completed = str_contains(mb_strtolower($value[0]), mb_strtolower($currentValues));
                            }

                            if ($typeCondition === "IN_NO") {
                                $completed = !$completed;
                            }
                        }
                        break;
                    case "KEEP":
                    case "KEEP_NO":
                        if ($currentValues && $multiple) {
                            if ($fieldType === "employee" || $fieldType === "crm") {
                                for ($k = 0; $k < count($value); $k++) {
                                    if ($fieldType === "employee") {
                                        $intersect = array_intersect($currentValues, User::getUserDepartments($value[$k]));
                                    } else {
                                        $convertValues = self::crmConvert($currentValues);
                                        $intersect = array_intersect($convertValues, $value[$k]);
                                    }

                                    $completed = ($typeCondition === "KEEP_NO") ? (count($intersect) === 0) : (count($intersect) === count($currentValues));

                                    if ($completed) {
                                        break;
                                    }
                                }
                            } elseif (
                                $fieldType === "iblock_section" || $fieldType === "iblock_element" ||
                                $fieldType === "enumeration" || $fieldType === "tags"
                            ) {
                                $currentInt = array_map("intval", $currentValues);

                                for ($k = 0; $k < count($value); $k++) {
                                    $valInteger = array_map("intval", $value[$k]);
                                    $intersect = array_intersect($currentInt, $valInteger);
                                    $completed = ($typeCondition === "KEEP_NO") ? (count($intersect) === 0) : (count($intersect) === count($currentInt));
                                    
                                    if ($completed) {
                                        break;
                                    }
                                }
                            } elseif ($fieldType === "string") {
                                $currentString = implode(" ", $currentValues);

                                for ($k = 0; $k < count($value); $k++) {
                                    $completed = ($typeCondition === "KEEP_NO") ? !str_contains(mb_strtolower($value[$k]), mb_strtolower($currentString)) : str_contains(mb_strtolower($value[$k]), mb_strtolower($currentString));

                                    if (
                                        ($typeCondition === "KEEP" && $completed) ||
                                        ($typeCondition === "KEEP_NO" && !$completed)
                                    ) {
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    case "SAME":
                    case "SAME_NO":
                        if ($currentValues) {
                            if ($fieldType === "boolean") {
                                $completed = ($typeCondition === "SAME_NO") ? ((int) $currentValues !== (int) $value) : ((int) $currentValues === (int) $value);
                            } elseif ($currentValues && $multiple) {
                                if ($fieldType === "employee" || $fieldType === "crm") {
                                    for ($k = 0; $k < count($value); $k++) {
                                        if ($fieldType === "crm") {
                                            $convertValues = self::crmConvert($currentValues);
                                            $currentValues = $convertValues;
                                        }

                                        $intersect = array_intersect($currentValues, $value[$k]);
                                        $completed = ($typeCondition === "SAME_NO") ? (count($intersect) !== count($currentValues)) : (count($intersect) === count($currentValues));

                                        if (
                                            ($typeCondition === "SAME" && $completed) ||
                                            ($typeCondition === "SAME_NO" && !$completed)
                                        ) {
                                            break;
                                        }
                                    }
                                } elseif (
                                    $fieldType === "iblock_section" || $fieldType === "iblock_element" ||
                                    $fieldType === "enumeration" || $fieldType === "tags"
                                ) {
                                    $currentInt = array_map("intval", $currentValues);

                                    for ($k = 0; $k < count($value); $k++) {
                                        $valInteger = array_map("intval", $value[$k]);
                                        $intersect = array_intersect($currentInt, $valInteger);
                                        $completed = ($typeCondition === "SAME_NO") ? (count($intersect) === 0) : (count($intersect) === count($currentInt));

                                        if (
                                            ($typeCondition === "SAME" && $completed) ||
                                            ($typeCondition === "SAME_NO" && !$completed)
                                        ) {
                                            break;
                                        }
                                    }
                                } elseif ($fieldType === "string") {
                                    $currentValues = array_map("mb_strtolower", $currentValues);

                                    for ($k = 0; $k < count($value); $k++) {
                                        $valueLower = array_map("mb_strtolower", $value[$k]);
                                        $intersect = array_intersect($currentValues, $valueLower);
                                        $completed = ($typeCondition === "SAME_NO") ? (count($intersect) === 0) : (count($intersect) > 0);

                                        if (
                                            ($typeCondition === "SAME" && $completed) ||
                                            ($typeCondition === "SAME_NO" && !$completed)
                                        ) {
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case "DATE":
                        if ($currentValues && in_array($fieldType, ["date", "datetime"])) {
                            $dateFormat = Date::getFormat();
                            $value = (int) $value + 1;
                            $days = "{$value} days";
                            $targetDate = new Date();
                            $targetDate->add($days);
                            $tDate = $targetDate->format($dateFormat);

                            if (!$multiple) {
                                try {
                                    $currentValDate = (new DateTime($currentValues))->format($dateFormat);
                                    $completed = (strtotime($currentValDate) > strtotime($tDate));
                                } catch (ObjectException $e) {
                                    break;
                                }
                            } else {
                                for ($k = 0; $k < count($currentValues); $k++) {
                                    try {
                                        $currentValDate = (new DateTime($currentValues[$k]))->format($dateFormat);
                                        $completed = (strtotime($currentValDate) > strtotime($tDate));
                                    } catch (ObjectException $e) {
                                        continue;
                                    }
                                    
                                    if ($completed) {
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    case "LESS":
                    case "LESS_OR":
                    case "BIG":
                    case "BIG_OR":
                        if ($currentValues && in_array($fieldType, ["integer", "double"])) {
                            $min = false;
                            $max = false;

                            if ($typeCondition === "LESS") {
                                $max = (float) $value - 1;
                            } elseif ($typeCondition === "LESS_OR") {
                                $max = (float) $value;
                            } elseif ($typeCondition === "BIG") {
                                $min = (float) $value + 1;
                            } elseif ($typeCondition === "BIG_OR") {
                                $min = (float) $value;
                            }

                            if (!$multiple) {
                                if ($min) {
                                    $completed = ((float) $currentValues >= $min);
                                } elseif ($max) {
                                    $completed = ((float) $currentValues <= $max);
                                }
                            } else {
                                for ($k = 0; $k < count($currentValues); $k++) {
                                    if ($min) {
                                        $completed = ((float) $currentValues[$k] >= $min);
                                    } elseif ($max) {
                                        $completed = ((float) $currentValues[$k] <= $max);
                                    }

                                    if ($completed) {
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    case "RANGE":
                        if ($currentValues && in_array($fieldType, ["integer", "double"])) {
                            if (!$multiple) {
                                for ($k = 0; $k < count($value); $k++) {
                                    $min = array_key_exists("min", $value[$k]) ? (float) $value[$k]["min"] : false;
                                    $max = array_key_exists("max", $value[$k]) ? (float) $value[$k]["max"] : false;

                                    if (
                                        (!$min && (float) $currentValues <= $max) ||
                                        (!$max && (float) $currentValues >= $min) ||
                                        ($min && $max && (float) $currentValues <= $max && (float) $currentValues >= $min)
                                    ) {
                                        $completed = true;
                                        break;
                                    }
                                }
                            } else {
                                for ($k = 0; $k < count($currentValues); $k++) {
                                    for ($i = 0; $i < count($value); $i++) {
                                        $min = array_key_exists("min", $value[$i]) ? (float) $value[$i]["min"] : false;
                                        $max = array_key_exists("max", $value[$i]) ? (float) $value[$i]["max"] : false;

                                        if (
                                            (!$min && (float) $currentValues <= $max) ||
                                            (!$max && (float) $currentValues >= $min) ||
                                            ($min && $max && (float) $currentValues <= $max && (float) $currentValues >= $min)
                                        ) {
                                            $completed = true;
                                            break;
                                        }
                                    }

                                    if ($completed) {
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                    default:
                        break;
                }
            }

            if ($completed) {
                break;
            }
        }

        return $completed;
    }

    /**
     * Checks an array of task fields
     * 
     * @param int $id
     * @param array $fields
     * @param array $taskData
     * @param array $currentValues
     */
    protected static function checkTask(int $id = 0, array $fields, array $taskData, array &$currentValues)
    {
        foreach ($taskData as $field => &$val) {
            if (array_key_exists($field, $fields)) {
                if ($field === "TAGS" && $val && $id > 0) {
                    $tags = TaskTagTable::getList([
                        "select" => ["ID", "TAG_ID"],
                        "filter" => ["TASK_ID" => $id]
                    ])->fetchAll();
                    $val = $tags ? array_column($tags, "TAG_ID") : [];
                } elseif ($fields[$field]["USER_TYPE_ID"] === "employee" && $val) {
                    if (gettype($val) === "array") {
                        foreach ($val as $k => $v) {
                            $val[$k] = "U" . $v;
                        }
                    } else {
                        $val = "U" . $val;
                    }
                }

                $currentValues[$field] = !$val ? null : $val;
            }
        }
    }

    /**
     * Converts it to a custom storage option for CRM elements.
     * 
     * @param string|array $val
     * @return string|array
     */
    protected static function crmConvert(string|array $val): string|array
    {
        if ($val) {
            $pattern = "/^T([a-zA-Z0-9]+)_.*$/";
            $matches = [];

            if (gettype($val) === "string") {
                if (str_starts_with("T", $val)) {
                    preg_match($pattern, $val, $matches);
                    $val = $matches ? str_replace("T" . $matches[1] . "_", "DYN_" . hexdec($matches[1]) . ":", $val) : $val;
                }
            } elseif (gettype($val) === "array") {
                foreach ($val as $k => $v) {
                    $matches = [];
                    preg_match($pattern, $v, $matches);
                    $val[$k] = $matches ? str_replace("T" . $matches[1] . "_", "DYN_" . hexdec($matches[1]) . ":", $v) : $v;
                }
            }
        }

        return $val;
    }
}