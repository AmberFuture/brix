<?php
namespace Brix\SecretSanta\Install;

use Bitrix\Main\Application;
use Bitrix\Main\DB\Exception;
use Brix\SecretSanta\Tables\SecretSantaTable;

/**
 * Class Db
 * Creating tables
 * 
 * @package Brix\SecretSanta\Install
 */
class Db
{
    /**
     * @var array
     */
    private static $TABLES = [SecretSantaTable::class];

    /**
     * Adding our table
     * 
     * @return void
     */
    public static function install()
    {
        try {
            $connection = Application::getConnection();

            foreach (self::$TABLES as $table) {
                if (!$connection->isTableExists($table::getTableName())) {
                    $table::getEntity()->createDbTable();
                }
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
    
    /**
     * Removing our table
     * 
     * @return void
     */
    public static function uninstall()
    {
        try {
            $connection = Application::getConnection();

            foreach (self::$TABLES as $table) {
                if ($connection->isTableExists($table::getTableName())) {
                    $connection->dropTable($table::getTableName());
                }
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }
}