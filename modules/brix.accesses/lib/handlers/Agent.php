<?php
namespace Brix\Handlers;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Brix\Config;
use Brix\Tables\HistoryTable;

/**
 * Class Iblock
 * 
 * @package Brix\Handlers
 */
class Agent
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.accesses";

    /**
     * Clears the history of access changes to information security
     * 
     * @return string|void
     */
    public static function historyClear()
    {
        if (!Loader::includeModule(self::MODULE_ID)) {
            return;
        }

        $period = (int) Config::historyclear();

        if ($period > 0) {
            $date = (new Date())->add("-{$period} days");

            $dbList = HistoryTable::getList([
                "select" => ["ID"],
                "filter" => ["<=DATE_MODIFIED" => $date]
            ])->fetchAll();

            if ($dbList) {
                foreach ($dbList as $item) {
                    HistoryTable::delete($item["ID"]);
                }
            }
        }

        return "\Brix\Handlers\Agent::historyClear();";
    }
}