<?php
namespace Brix\SecretSanta;

use BadMethodCallException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Json;
use CTimeZone;
use InvalidArgumentException;

/**
 * Class Config
 * 
 * @method static gamename()
 * @method static dateregistration()
 * @method static datestart()
 * @method static datecompletion()
 * @method static noticeregistration()
 * @method static noticewish()
 * @method static noticestart()
 * @method static noticedate()
 * @method static noticeadditional()
 * @method static enablenoticecompletion()
 * @method static noticecompletion()
 * @method static errors()
 * @method static setGamename($value)
 * @method static setDateregistration($value)
 * @method static setDatestart($value)
 * @method static setDatecompletion($value)
 * @method static setNoticeregistration($value)
 * @method static setNoticewish($value)
 * @method static setNoticestart($value)
 * @method static setNoticedate($value)
 * @method static setNoticeadditional($value)
 * @method static setEnablenoticecompletion($value)
 * @method static setNoticecompletion($value)
 * @method static setErrors($value)
 * @package Brix\SecretSanta
 */

class Config
{
    /**
     * @var string
     */
    const MODULE_ID = "brix.secretsanta";

    /**
     * List of available methods, "set" methods are also available to these methods
     * @var array
     */
    const AVAILABLE_METHODS = [
        "gamename",
        "dateregistration",
        "datestart",
        "datecompletion",
        "noticeregistration",
        "noticewish",
        "noticestart",
        "noticedate",
        "noticeadditional",
        "enablenoticecompletion",
        "noticecompletion",
        "errors"
    ];

    /**
     * Options with dates
     * @var array
     */
    const OPTIONS_DATE = [
        "dateregistration",
        "datestart",
        "datecompletion",
        "noticedate"
    ];

    /**
     * Options with boolean
     * @var array
     */
    const OPTIONS_BOOLEAN = [
        "enablenoticecompletion"
    ];

    /**
     * Magic method for working with module settings.
     * 
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $substr_0_3 = strtolower(substr($name, 0, 3));
        $isSetter = "set" === $substr_0_3;
        $validatedName = $isSetter ? substr($name, 3) : $name;
        $validatedName = strtolower($validatedName);
        $match = array_filter(self::AVAILABLE_METHODS, function($strMethod) use ($validatedName) {
            return strtolower($strMethod) === $validatedName;
        });

        if (empty($match)) { 
            throw new BadMethodCallException($name);
        }

        if ($isSetter && empty($arguments)) {
            throw new InvalidArgumentException();
        }

        if ($isSetter) {
            self::set($validatedName, $arguments[0]);
        } else {
            return self::get($validatedName);
        }
    }

    /**
     * Getting default value of option
     * 
     * @param string $name
     * @return mixed
     */
    public static function getDefaultValue(string $name): mixed
    {
        $optionsDefault = Option::getDefaults(self::MODULE_ID);

        return array_key_exists($name, $optionsDefault) ? $optionsDefault[$name] : "";
    }

    /**
     * Setting default values of options
     * 
     * @return void
     */
    public static function setDefaultOptions(): void
    {
        $optionsDefault = Option::getDefaults(self::MODULE_ID);
        Option::delete(self::MODULE_ID);

        foreach ($optionsDefault as $name => $value) {
            Option::set(self::MODULE_ID, $name, $value);
        }
    }

    /**
     * Deletes the module option
     * 
     * @return void
     */
    public static function delete(string $name): void
    {
        Option::delete(self::MODULE_ID, ["name" => $name]);
    }

    /**
     * Getting the value of a variable by name
     * 
     * @param string $name
     * @return mixed
     */
    protected static function get(string $name): mixed
    {
        $value = Option::get(self::MODULE_ID, $name) ?? self::getDefaultValue($name);

        if (!empty($value)) {
            if ($name === "errors") {
                $value = Json::decode($value);
            } elseif (in_array($name, self::OPTIONS_DATE)) {
                $value += CTimeZone::GetOffset();
            }
        }

        return $value;
    }

    /**
     * Set the value of a variable and if necessary delete the menu cache
     * 
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected static function set(string $name, $value = ""): void
    {
        if (empty($value)) {
            $value = in_array($name, self::OPTIONS_BOOLEAN) ? "N" : self::getDefaultValue($name);
        } elseif ($name === "errors") {
            $value = Json::encode($value);
        }

        if (in_array($name, self::OPTIONS_DATE)) {
            $value = $value ? (strtotime($value) - CTimeZone::GetOffset()) : $value;
            self::updateAgent($name, $value);
        }

        Option::set(self::MODULE_ID, $name, $value);
    }

    /**
     * Updates agent launch dates
     * 
     * @param string $name
     * @param string $value
     * @return void
     */
    protected static function updateAgent(string $name, string $value = ""): void
    {
        $function = "";

        switch ($name) {
            case "dateregistration":
                $function = "\Brix\SecretSanta\Handlers\Agent::registrationNotice();";
                break;
            case "datestart":
                $function = "\Brix\SecretSanta\Handlers\Agent::startNotice();";
                break;
            case "datecompletion":
                $function = "\Brix\SecretSanta\Handlers\Agent::completion();";
                break;
            case "noticedate":
                $function = "\Brix\SecretSanta\Handlers\Agent::additionalNotice();";
                break;
            default:
                break;
        }

        $res = \CAgent::GetList([], ["MODULE_ID" => self::MODULE_ID, "NAME" => $function]);
        $value = $value ? ($value + CTimeZone::GetOffset()) : "";

        if (!empty($value)) {
            $dateTimeFormat = DateTime::getFormat();
            $date = DateTime::createFromTimestamp($value)->format($dateTimeFormat);

            if ($agent = $res->fetch()) {
                \CAgent::Update($agent["ID"], ["NEXT_EXEC" => $date]);
            } else {
                \CAgent::AddAgent(
                    $function,
                    self::MODULE_ID,
                    "Y",
                    "0",
                    "",
                    "Y",
                    $date
                );
            }
        } else {
            if ($agent = $res->fetch()) {
                \CAgent::Delete($agent["ID"]);
            }
        }
    }
}