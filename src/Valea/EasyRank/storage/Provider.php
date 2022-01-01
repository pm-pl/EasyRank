<?php

declare(strict_types=1);

namespace Valea\EasyRank\storage;

use Valea\EasyRank\api\Rank;
use pocketmine\player\Player;

abstract class Provider {

    abstract public function getRanks(): array;

    abstract public function createRank(string $id, string $displayName, array $permissions = [], ?string $chatFormat = null, ?string $nameTagFormat = null, array $inherit = []): Rank;

    abstract public function getPlayerRank(Player $player): ?Rank;

    abstract public function setPlayerRank(Player $player, Rank $rank);

    abstract public function isPlayerRanked(Player $player): bool; // if the player has not got a rank stored

    abstract public function getPermissionsFromId(string $id): array; // permissions arrays are "node_as_string" => bool

    abstract public function setField(Rank $rank, string $field, $value); // fields are displayName (string), chatFormat (string), nameTagFormat (string), permissions ("node_as_string" => bool array), inherit (string[])  

    abstract public function getField(Rank $rank, string $field);

    abstract public function deleteRank(Rank $rank);
}