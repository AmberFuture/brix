<?php
namespace Brix\SecretSanta\Helpers;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\{Date, DateTime};
use Bitrix\Pull\Push;
use Brix\SecretSanta\Config;
use CIMNotify;

/**
 * Class Notifications
 * 
 * @package Brix\SecretSanta\Helpers
 */
final class Notifications
{
    /**
     * @var string
     */
    const MODULE_ID  = "brix.secretsanta";

    /**
     * Sends an invitation to participate
     * 
     * @param array $users
     * @return void
     */
    public static function invitation(array $users = []): void
    {
        if (!empty($users) && Base::isModules() && Base::isRegistration()) {
            $text = static::replacement(Config::noticeregistration());
            $arFields = [
                "NOTIFY_EVENT" => "invite",
                "NOTIFY_MESSAGE" => $text,
                "NOTIFY_SUB_TAG" => self::MODULE_ID . "|invite",
                "NOTIFY_BUTTONS" => [
                    [
                        "TITLE" => Loc::getMessage("BRIX_SECRETSANTA_HELPERS_NOTIFICATIONS_ACCEPT"),
                        "VALUE" => "Y",
                        "TYPE" => "accept"
                    ],
                    [
                        "TITLE" => Loc::getMessage("BRIX_SECRETSANTA_HELPERS_NOTIFICATIONS_CANCEL"),
                        "VALUE" => "N",
                        "TYPE" => "cancel"
                    ]
                ]
            ];

            foreach ($users as $id) {
                $arFields["TO_USER_ID"] = $id;
                $arFields["NOTIFY_TAG"] = self::MODULE_ID . "|INVITE|" . $id;

                static::send($arFields);
            }
        }
    }

    /**
     * Notification of the need to fill out the wishlist
     * 
     * @param int $userId
     * @return void
     */
    public static function wish(int $userId): void
    {
        $text = static::replacement(Config::noticewish());
        $tag = self::MODULE_ID . "|WISH|" . $userId;
        $arFields = [
            "TO_USER_ID" => $userId,
            "NOTIFY_TAG" => $tag,
            "NOTIFY_MESSAGE" => $text
        ];

        static::send($arFields);
    }

    /**
     * Notification of player allocation
     * 
     * @param array $arPlayers
     * @return void
     */
    public static function start(array $arPlayers = []): void
    {
        if (Loader::includeModule("im")) {
            \CIMNotify::DeleteBySubTag(self::MODULE_ID . "|invite");
        }

        if (!empty($arPlayers)) {
            $arNames = User::getUserFormated(array_values($arPlayers));
            $text = static::replacement(Config::noticestart());

            foreach ($arPlayers as $playerId => $recipientId) {
                $name = $arNames[$recipientId];
                $recipientLink = '<a href="/company/personal/user/' . $recipientId . '/">' . $name . '</a>';
                $tag = self::MODULE_ID . "|START|" . $playerId;
                $arFields = [
                    "TO_USER_ID" => $playerId,
                    "NOTIFY_TAG" => $tag,
                    "NOTIFY_MESSAGE" => mb_ereg_replace("#USER_NAME#", $recipientLink, $text),
                    "NOTIFY_MESSAGE_OUT" => mb_ereg_replace("#USER_NAME#", $name, $text)
                ];

                static::send($arFields);
            }
        }
    }

    /**
     * Additional notification to players
     * 
     * @param array $arPlayers
     * @return void
     */
    public static function additional(array $arPlayers = []): void
    {
        if (!empty($arPlayers)) {
            $text = static::replacement(Config::noticeadditional());

            foreach ($arPlayers as $playerId) {
                $arFields = [
                    "TO_USER_ID" => $playerId,
                    "NOTIFY_MESSAGE" => $text,
                    "NOTIFY_MESSAGE_OUT" => $text
                ];

                static::send($arFields);
            }
        }
    }

    /**
     * Notifying participants of the end of the game
     * 
     * @param array $arPlayers
     * @return void
     */
    public static function completion(array $arPlayers = []): void
    {
        if (!empty($arPlayers) && Config::enablenoticecompletion() === "Y") {
            $text = static::replacement(Config::noticecompletion());

            foreach ($arPlayers as $playerId) {
                $arFields = [
                    "TO_USER_ID" => $playerId,
                    "NOTIFY_MESSAGE" => $text,
                    "NOTIFY_MESSAGE_OUT" => $text
                ];

                static::send($arFields);
            }
        }
    }

    /**
     * Replaces modifiers
     * 
     * @param string $text
     * @return string
     */
    private static function replacement(string $text): string
    {
        $gameName = Config::gamename();
        $dateFormat = Date::getFormat();
        $dateTimeFormat = DateTime::getFormat();
        $dateTimeFormat = str_replace([":ss", ":s"], "", $dateTimeFormat);
        $dateStart = Config::datestart() ? DateTime::createFromTimestamp(Config::datestart())->format($dateTimeFormat) : "";
        $dateEnd = Config::datecompletion() ? DateTime::createFromTimestamp(Config::datecompletion())->format($dateFormat) : "";

        $text = mb_ereg_replace("#GAME_NAME#", $gameName, $text);
        $text = mb_ereg_replace("#DATE_START#", $dateStart, $text);
        $text = mb_ereg_replace("#DATE_END#", $dateEnd, $text);

        return $text;
    }

    /**
     * Sends a notification
     * 
     * @param $arFields
     */
    private static function send(array $arFields)
    {
        if (Loader::includeModule("im")) {
            if ($arFields["NOTIFY_EVENT"] === "invite") {
                $arFields["NOTIFY_TYPE"] = IM_NOTIFY_CONFIRM;
            }

            $arFields["NOTIFY_MODULE"] = self::MODULE_ID;
            $arFields["SYSTEM"] = "Y";

            CIMNotify::Add($arFields);

            if (Loader::includeModule("pull") && Option::get("pull", "push") === "Y") {
                Push::add([$arFields["TO_USER_ID"]], [
                    "module_id" => self::MODULE_ID,
                    "push" => [
                        "message" => $arFields["NOTIFY_MESSAGE"]
                    ]
                ]);
            }
        }
    }
}
