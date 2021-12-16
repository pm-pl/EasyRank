<?php

namespace Valea\EasyRank\listeners;

use Valea\EasyRank\Main;
use Valea\EasyRank\api\Rank;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class Join implements Listener
{
    public function onJoin(PlayerJoinEvent $event) {
        if (Main::$storageProvider->isPlayerRanked($event->getPlayer())) {
            $rank = Rank::getPlayerRank($event->getPlayer());
            $rank->addPermissions($event->getPlayer());
        } else {
            $rank = Rank::get("default");
            $rank->giveToPlayer($event->getPlayer());
        }
        $event->getPlayer()->setNameTag(
            str_replace("%name%", $event->getPlayer()->getDisplayName(), $rank->getNameTagFormat())
        );
    }
}