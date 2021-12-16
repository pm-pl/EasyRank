<?php

namespace Valea\EasyRank\listeners;

use Valea\EasyRank\Main;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class Leave implements Listener
{
    public function onQuit(PlayerQuitEvent $event) {
        if (isset(Main::$attachments[$event->getPlayer()->getUniqueId()->toString()])) {
            unset(Main::$attachments[$event->getPlayer()->getUniqueId()->toString()]);
        }
    }
}