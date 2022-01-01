<?php

declare(strict_types=1);

namespace Valea\EasyRank\storage\providers;

use Exception;
use InvalidArgumentException;
use Valea\EasyRank\api\Rank;
use Valea\EasyRank\Main;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use Valea\EasyRank\storage\Provider;

class ConfigProvider extends Provider {

    private Config $players;

    private Config $ranks;

    public function __construct()
    {
        $this->players = new Config(Main::$instance->getDataFolder() . "player.json", Config::JSON);
        $this->ranks = new Config(Main::$instance->getDataFolder(). "ranks.yml", Config::JSON, [
            "default" => [
                "displayName" => "Player",
                "chatFormat" => "%name%§b >> §7%msg%",
                "nameTagFormat" => "%name%",
                "permissions" => [
                    "example.default" => true
                ]
            ],
            "admin" => [
                "displayName" => "Admin",
                "chatFormat" => "§4[§cAdmin§4] §c%name% §b>> §f%msg%",
                "nameTagFormat" => "§4[§cAdmin§4] §c%name%",
                "permissions" => [
                    "example.admin" => true
                ],
                "inherit" => ["default"]
            ],
        ]);
    }
    
    public function getPlayerRank(Player $player): ?Rank
    {
        return $this->generateRankFromArray(
            $this->players->get(
                (string)$player->getUniqueId(), "default"
            ),
            $this->ranks->get(
                $this->players->get((string)$player->getUniqueId(), "default") 
            )
        );
    }

    /** @return Rank[] */
    public function getRanks(): array
    {
        $builder = [];
        foreach ($this->ranks->getAll() as $id => $data) {
            $builder[$id] = $this->generateRankFromArray($id, $data);
        }
        return $builder;
    }

    public function setPlayerRank(Player $player, Rank $rank)
    {
        $this->players->set((string)$player->getUniqueId(), $rank->getId());
        $this->players->save();
    
    }

    private function generateRankFromArray(string $id, array $data) {
        if (!isset($data["inherit"])) {
            $data["inherit"] = [];
        }
        return new Rank(
            $id, $data["permissions"], $data["inherit"]
        );
    }

    public function isPlayerRanked(Player $player): bool {
        return $this->players->exists((string)$player->getUniqueId());
    }

    public function getPermissionsFromId(string $id): array
    {
        return $this->ranks->get($id)["permissions"];
    }

    public function setField(Rank $rank, string $field, $value) {
        $data = $this->ranks->get($rank->getId(), null);
        if ($data === null) {
            throw new Exception("This shouldn't happen!");
        }

        $data[$field] = $value;
        $this->ranks->set($rank->getId(), $data);
        $this->ranks->save();
    }

    public function deleteRank(Rank $rank)
    {
        foreach ($this->players->getAll() as $id => $rankId) {
            if ($rankId == $rank->getId()) {
                $this->players->remove($id);
            }
        }
        $this->ranks->remove($rank->getId());
    }

    public function getField(Rank $rank, string $field) {
        return $this->ranks->get($rank->getId(), null)[$field];
    }

    public function createRank(string $id, string $displayName, array $permissions = [], ?string $chatFormat = null, ?string $nameTagFormat = null, array $inherit = []): Rank {

        if ($this->ranks->get($id, null) !== null) {
            throw new InvalidArgumentException("Rank ID " . $id . " is not unique.");
        }

        if ($chatFormat === null) {
            $chatFormat = $displayName . " %name%§b >> §7%msg%";
        }
        if ($nameTagFormat === null) {
            $chatFormat = $displayName . " §r%name%";
        }

        $this->ranks->set($id, [
            "displayName" => $displayName,
            "chatFormat" => $chatFormat,
            "nameTagFormat" => $nameTagFormat,
            "permissions" => $permissions,
            "inherit" => $inherit
        ]);
        $this->ranks->save();

        return $this->getRanks()[$id];

    }

}