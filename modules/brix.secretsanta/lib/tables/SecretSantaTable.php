<?php
namespace Brix\SecretSanta\Tables;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\{BooleanField, IntegerField, TextField};
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserTable;
use Brix\SecretSanta\Helpers\{Base, Notifications};

/**
* Class SecretSantaTable
*
* @package Brix\SecretSanta\Tables
*/

class SecretSantaTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     * 
     * @return string
     */
    public static function getTableName()
    {
        return "brix_secret_santa";
    }
    
    /**
     * Returns entity map definition.
     * 
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField("PLAYER", [
                "primary" => true,
                "title" => Loc::getMessage("BRIX_SECRETSANTA_TABLE_PLAYER")
            ]),
            new BooleanField("ACTIVE", [
                "values" => ["N", "Y"],
                "default_value" => "Y",
                "title" => Loc::getMessage("BRIX_SECRETSANTA_TABLE_ACTIVE")
            ]),
            new IntegerField("RECIPIENT", [
                "nullable" => true,
                "title" => Loc::getMessage("BRIX_SECRETSANTA_TABLE_RECIPIENT")
            ]),
            new BooleanField("TAKE_PART", [
                "values" => ["N", "Y"],
                "default_value" => "N",
                "title" => Loc::getMessage("BRIX_SECRETSANTA_TABLE_TAKE_PART")
            ]),
            new TextField("WISHLIST", [
                "nullable" => true,
                "title" => Loc::getMessage("BRIX_SECRETSANTA_TABLE_WISHLIST")
            ]),
            new Reference(
                "PLAYER_USER",
                UserTable::class,
                Join::on("this.PLAYER", "ref.ID")
            ),
            new Reference(
                "RECIPIENT_USER",
                UserTable::class,
                Join::on("this.RECIPIENT", "ref.ID")
            )
        ];
    }

    /**
     * Returns the list of participants
     * 
     * @return array
     */
    public static function getTakeParts(): array
    {
        $db = static::getList([
            "select" => ["PLAYER"],
            "filter" => [
                "ACTIVE" => "Y",
                "TAKE_PART" => "Y"
            ]
        ])->fetchAll();

        return array_column($db, "PLAYER");
    }

    /**
     * Updates the activity of a record by user or sends an invitation
     * 
     * @param int $userId
     * @param string $active
     * @param array $arDep
     * @return void
     */
    public static function updateActivePlayer(int $userId, string $active, array $arDep): void
    {
        if (Base::isModules()) {
            $row = static::getById($userId)->fetch();

            if ($row) {
                if (
                    (!empty($arDep) && $row["ACTIVE"] !== $active) || 
                    (empty($arDep) && $active === "N")
                ) {
                    static::update($userId, ["ACTIVE" => $active]);
                }
            } elseif (!$row && $active === "Y" && !empty($arDep) && Base::isRegistration()) {
                Notifications::invitation([$userId]);
            }
        }
    }

    /**
     * Deletes and updates user records
     * 
     * @param int $userId
     * @return void
     */
    public static function clearDeletedUser(int $userId): void
    {
        $db = static::getList([
            "select" => ["PLAYER", "RECIPIENT"],
            "filter" => [
                [
                    "LOGIC" => "OR",
                    ["PLAYER" => $userId],
                    ["RECIPIENT" => $userId]
                ]
            ]
        ])->fetchAll();

        foreach ($db as $item) {
            if ((int) $item["PLAYER"] === $userId) {
                static::delete($userId);
            } elseif ((int) $item["RECIPIENT"] === $userId) {
                static::update($item["PLAYER"], ["RECIPIENT" => null]);
            }
        }
    }

    /**
     * Distributes the players
     * 
     * @return void
     */
    public static function distributionOfPlayers(): void
    {
        if (Base::isModules()) {
            $db = static::getList([
                "select" => ["PLAYER"],
                "filter" => ["ACTIVE" => "Y", "TAKE_PART" => "Y", "RECIPIENT" => null]
            ])->fetchAll();
            $idPlayers = array_column($db, "PLAYER");

            if (count($idPlayers) > 1) {
                $arPlayers = [];
                $max = count($idPlayers) - 1;
                shuffle($idPlayers);

                foreach ($idPlayers as $k => $id) {
                    $recipient = ((int) $k < (int) $max) ? ((int) $k + 1) : 0;
                    static::update((int) $id, ["RECIPIENT" => $idPlayers[$recipient]]);
                    $arPlayers[$id] = $idPlayers[$recipient];
                }
                
                Notifications::start($arPlayers);
            }
        }
    }

    /**
     * Checks for unallocated participants and returns an array with their id
     * 
     * @return array
     */
    public static function checkUnallocatedPlayers(): array
    {
        $arPlayers = [];

        if (Base::isModules()) {
            $db = static::getList([
                "select" => [
                    "PLAYER",
                    "LINKED_RECIPIENT_ACTIVE" => "LINKED_RECIPIENT.ACTIVE",
                    "LINKED_RECIPIENT_USER" => "LINKED_RECIPIENT.PLAYER"
                ],
                "filter" => [
                    "TAKE_PART" => "Y",
                    "ACTIVE" => "Y",
                    [
                        "LOGIC" => "OR",
                        ["LINKED_RECIPIENT_ACTIVE" => "N"],
                        ["LINKED_RECIPIENT_USER" => null]
                    ]
                ],
                "runtime" => [
                    "LINKED_RECIPIENT" => [
                        "data_type" => __CLASS__,
                        "reference" => ["this.PLAYER" => "ref.RECIPIENT"]
                    ]
                ]
            ])->fetchAll();
            $arPlayers = array_column($db, "PLAYER");
        }

        return $arPlayers;
    }

    /**
     * Gets players who don't have a recipient
     * 
     * @return array
     */
    public static function checkRecipientPlayers(): array
    {
        $db = static::getList([
            "select" => [
                "PLAYER",
                "LINKED_PLAYER_ACTIVE" => "LINKED_PLAYER.ACTIVE"
            ],
            "filter" => [
                "TAKE_PART" => "Y",
                "ACTIVE" => "Y",
                [
                    "LOGIC" => "OR",
                    ["RECIPIENT" => null],
                    ["LINKED_PLAYER_ACTIVE" => "N"]
                ]
            ],
            "runtime" => [
                "LINKED_PLAYER" => [
                    "data_type" => __CLASS__,
                    "reference" => ["this.RECIPIENT" => "ref.PLAYER"]
                ]
            ]
        ])->fetchAll();

        return array_column($db, "PLAYER");
    }

    /**
     * Distributes the players
     * 
     * @return array
     */
    public static function distributionUnallocatedPlayers(): array
    {
        $arResult = [
            "error" => false,
            "text" => ""
        ];
        $idRecipients = static::checkUnallocatedPlayers();
        
        if (count($idRecipients) === 0) {
            $arResult = [
                "error" => true,
                "text" => Loc::getMessage("BRIX_SECRETSANTA_TABLE_ERROR_NOT_PLAYERS")
            ];
        } else {
            $db = static::getList([
                "select" => ["PLAYER"],
                "filter" => ["RECIPIENT" => $idRecipients]
            ])->fetchAll();

            foreach ($db as $player) {
                static::update($player["PLAYER"], ["RECIPIENT" => null]);
            }
            
            $arPlayers = [];
            $idPlayers = static::checkRecipientPlayers();
            $count = min(count($idRecipients), count($idPlayers));
            $arResult["text"] = (count($idRecipients) === count($idPlayers)) ? Loc::getMessage("BRIX_SECRETSANTA_TABLE_SUCCESS") : Loc::getMessage("BRIX_SECRETSANTA_TABLE_ERROR_NOT_ALL");
            shuffle($idRecipients);
            shuffle($idPlayers);
            
            for ($i = 0; $i < $count; $i++) {
                if ((int) $idPlayers[$i] !== (int) $idRecipients[$i]) {
                    $arPlayers[$idPlayers[$i]] = $idRecipients[$i];
                    SecretSantaTable::update((int) $idPlayers[$i], ["RECIPIENT" => $idRecipients[$i]]);
                }
            }

            if (!empty($arPlayers)) {
                Notifications::start($arPlayers);
            } else {
                $arResult = [
                    "error" => true,
                    "text" => Loc::getMessage("BRIX_SECRETSANTA_TABLE_ERROR_IMPOSSIBLE")
                ];
            }
        }

        return $arResult;
    }

    /**
     * Notifying players
     * 
     * @return void
     */
    public static function notifyingPlayers(): void
    {
        if (Base::isModules()) {
            $db = static::getList([
                "select" => ["PLAYER"],
                "filter" => ["ACTIVE" => "Y", "TAKE_PART" => "Y"]
            ])->fetchAll();
            $idPlayers = array_column($db, "PLAYER");
            Notifications::additional($idPlayers);
        }
    }


    /**
     * Clears the table of participants
     * 
     * @return void
     */
    public static function clearTable(): void
    {
        $db = static::getList([])->fetchAll();
        $idPlayers = [];

        foreach ($db as $item) {
            if ($item["ACTIVE"] === "Y" && $item["TAKE_PART"] === "Y") {
                $idPlayers[] = $item["PLAYER"];
            }

            static::delete($item["PLAYER"]);
        }

        Notifications::completion($idPlayers);
    }
}