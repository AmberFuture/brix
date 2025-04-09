<?php
namespace Brix;

use BadMethodCallException;
use Bitrix\Main\Config\Option;
use Brix\Helpers\Access;
use InvalidArgumentException;

/**
 * Class Config
 * 
 * @method static historysave()
 * @method static historyclear()
 * @method static viewing()
 * @method static vhistory()
 * @method static changing()
 * @method static setHistorysave($value)
 * @method static setHistoryclear($value)
 * @method static setViewing($value)
 * @method static setVhistory($value)
 * @method static setChanging($value)
 * @package Brix
 */
class Config
{
    /**
     * @var string
     */
    const MODULE_ID = "brix.accesses";

    /**
     * List of available methods, "set" methods are also available to these methods
     * @var array
     */
    const AVAILABLE_METHODS = [
        "historysave",
        "historyclear",
        "viewing",
        "vhistory",
        "changing"
    ];

    /**
     * Boolean options
     * @var array 
     */
    const BOOL_OOPTIONS = [
        "historysave"
    ];

    /**
     * Accesses options
     * @var array 
     */
    const ACCESS_OOPTIONS = [
        "viewing",
        "vhistory",
        "changing"
    ];

    /**
     * Getting the value of a variable by name
     * 
     * @param string $name
     * @return mixed
     */
    protected static function get(string $name): mixed
    {
        $value = Option::get(self::MODULE_ID, $name) ?? self::getDefaultValue($name);

        if (array_key_exists($name, Access::getRights())) {
            $value = !empty($value) ? explode(",", $value) : [];
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
        if (array_key_exists($name, Access::getRights()) && !empty($value)) {
            $value = implode(",", $value);
        } elseif (array_key_exists($name, self::BOOL_OOPTIONS) && empty($value)) {
            $value = self::getDefaultValue($name);
        }

        Option::set(self::MODULE_ID, $name, $value);

        if (in_array($name, self::ACCESS_OOPTIONS)) {
            BXClearCache(true, "/bitrix/menu/");
        }
    }

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
    public static function getDefaultValue($name): mixed
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
}