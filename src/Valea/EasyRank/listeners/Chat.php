<?php

namespace Valea\EasyRank\listeners;

use Valea\EasyRank\api\Rank;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class Chat implements Listener {

    public function onChat(PlayerChatEvent $event) {
        
        $event->setFormat(
            str_replace(
                "%msg%",
                $event->getMessage(),
                str_replace(
                    "%name%", 
                    $event->getPlayer()->getDisplayName(), 
                    Rank::getPlayerRank($event->getPlayer())->getChatFormat()
                ) 
            )
        );

    }

}