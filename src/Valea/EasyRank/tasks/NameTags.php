<?php

namespace Valea\EasyRank\tasks;

use Valea\EasyRank\api\Rank;
use Valea\EasyRank\Main;
use pocketmine\scheduler\Task;

class NameTags extends Task {

    public function onRun(): void
    {
        foreach (Main::$instance->getServer()->getOnlinePlayers() as $player) {
            if (Rank::$provider->isPlayerRanked($player)) {
                $rank = Rank::getPlayerRank($player);
                $player->setNameTag(
                    str_replace("%name%", $player->getDisplayName(), $rank->getChatFormat())
                );
            }
        }
    }

}