<?php
namespace Brix\Install;

use Bitrix\Main\UrlRewriter;

/**
 * Class Db
 * Creating tables
 * 
 * @package Brix\Install
 */
class UrlRewrite
{
    /**
     * Array of processing to be created
     * 
     * @return array
     * [
     *  [
     *      "CONDITION" => "",
     *      "RULE" => "",
     *      "ID" => "",
     *      "PATH" => "",
     *      "SORT" => ""
     *  ]
     * ]
     */
    private static function getRules()
    {
        return [
            [
                "CONDITION" => "#^/brix_accesses/#",
                "RULE" => "",
                "ID" => "brix:accesses",
                "PATH" => "/brix_accesses/index.php",
                "SORT" => 100,
            ]
        ];
    }

    /**
     * Creating address processing rules
     * 
     * @return void
     */
    public static function install()
    {
        $siteId = defined(SITE_ID) ? SITE_ID : "s1";

        foreach (self::getRules() as $rule) {
            UrlRewriter::add($siteId, $rule);
        }
    }
    
    /**
     * Removing address processing rules
     * 
     * @return void
     */
    public static function uninstall()
    {
        $siteId = defined(SITE_ID) ? SITE_ID : "s1";
        
        foreach (self::getRules() as $rule) {
            UrlRewriter::delete($siteId, [
                "CONDITION" => $rule["CONDITION"],
                "ID" => $rule["ID"]
            ]);
        }
    }
}