<?php

namespace Valea\EasyRank\commands;

use InvalidArgumentException;
use Valea\EasyRank\api\Rank;
use Valea\EasyRank\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class EasyRank implements CommandExecutor
{
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (count($args) == 0) {
            $sender->sendMessage("EasyRank\n/er player <player>\n/er rank <rank id>\n/er rank create");
            return false;
        }
        switch ($args[0]) {

            case "rank": {
                if (isset($args[1])) {
                    if ($args[1] === "create") {
                        if (!isset($args[2]) && !isset($args[3])) {
                            $sender->sendMessage("EasyRank\n/er rank <rank_id>\n/er rank create <rank_id> <display_name>");
                            return false;
                        }

                        try {
                            $rank = Rank::create($args[2], $args[3]);
                        } catch (InvalidArgumentException $ex) {
                            $sender->sendMessage($ex->getMessage());
                        }

                        $sender->sendMessage("Rank " . $rank->getDisplayName() . "§r (" . $rank->getId() . ") successfully created.");

                    } else {
                        try {
                            $rank = Rank::get($args[1]);
                        } catch (InvalidArgumentException $ex) {
                            $sender->sendMessage($ex->getMessage());
                            return false;
                        }

                        if (isset($args[2])) {
                            switch ($args[2]) {
                                case "edit": {
                                    if (isset($args[3]) && isset($args[4])) {
                                        switch ($args[3]) {
                                            case "displayName": {

                                                $rank->setDisplayName($args[4]);
                                                
                                                break;
                                            }
                                            case "chatFormat": {

                                                $rank->setChatFormat($args[4]);
                                                
                                                break;
                                            }
                                            case "nameTagFormat": {

                                                $rank->setDisplayName($args[4]);
                                                
                                                break;
                                            }
                                        }
                                    } else {
                                        $sender->sendMessage("/er rank <rank id> edit <edit> <value>");
                                        $sender->sendMessage("Possible rank edits are: displayName, chatFormat, nameTagFormat");
                                        return false;
                                    }
                                    break;
                                }
                                case "setperm": {
                                    if (isset($args[3])) {
                                        $perms = $rank->getPermissions();

                                        if (isset($args[4]) && $args[4] == "false") {
                                            $perms[$args[3]] = false;
                                            $value = false;
                                        } else {
                                            $perms[$args[3]] = true;
                                            $value = true;
                                        }
                                        $sender->sendMessage($rank->getDisplayName() . " now has the permission " . $args[3] . " set to " . ($value ? "true" : "false"));

                                        $rank->setPermissions($perms);
                                    } else {
                                        $sender->sendMessage("/er rank <rank id> setperm <node> [true/false]");
                                        return false;
                                    }
                                    break;
                                }
                                case "rmperm": {
                                    if (isset($args[3])) {
                                        $perms = $rank->getPermissions();

                                        if (isset($perms[$args[3]])) {
                                            unset($perms[$args[3]]);
                                            $sender->sendMessage($rank->getDisplayName() . " no longer has the permission " . $args[3]);
                                        } else {
                                            $sender->sendMessage($rank->getDisplayName() . " does not have the permission " . $args[3] . ", nothing changed.");
                                        }

                                        $rank->setPermissions($perms);
                                    }else {
                                        $sender->sendMessage("/er rank <rank id> rmperm <node>");
                                        return false;
                                    }
                                    break;
                                }
                                case "getperms": {
                                    $perms = $rank->getPermissions();

                                    $out = $rank->getDisplayName() . "'s permissions:\n";

                                    foreach ($perms as $node => $value) {
                                        $out = $out . TextFormat::GRAY . $node . TextFormat::BLUE . ": " . TextFormat::AQUA . ($value ? "true" : "false") . "\n"; 
                                    }

                                    $sender->sendMessage($out);
                                    
                                    break;
                                }
                                case "addinherit": {
                                    if (isset($args[3])) {
                                        $inherit = $rank->getInherit();

                                        $inherit[] = $args[3];

                                        if (isset(Rank::$provider->getRanks()[$args[3]])) {
                                            $rank->setInherit($inherit);
                                            $sender->sendMessage($rank->getDisplayName() . " now inherits from " . $args[3]);
                                        } else {
                                            $sender->sendMessage("Rank " . $args[3] . " does not exist.");
                                        }
                                    } else {
                                        $sender->sendMessage("/er rank <rank id> addinherit <rank to inherit from>");
                                        return false;
                                    }
                                    break;
                                }
                                case "rminherit": {
                                    if (isset($args[3])) {
                                        $inherit = $rank->getInherit();

                                        $succeed = false;

                                        foreach ($inherit as $index => $id) {
                                            if ($id == $args[3]) {
                                                $succeed = true;
                                                unset($inherit[$index]);
                                            }
                                        }

                                        $rank->setInherit($inherit);

                                        if ($succeed) {
                                            $sender->sendMessage($rank->getDisplayName() . TextFormat::RESET . " no longer inherits from " . $args[3]);
                                        } else {
                                            $sender->sendMessage($rank->getDisplayName() . TextFormat::RESET . " does not inherit from ". $args[3] . ", nothing changed.");
                                        }
                                    } else {
                                        $sender->sendMessage("/er rank <rank id> rminherit <rank to remove>");
                                        return false;
                                    }
                                    break;
                                }
                                case "getinherit": {
                                    
                                        $groups = $rank->getInherit();

                                        $out = $rank->getDisplayName() . "'s inherited groups:\n";

                                        foreach ($groups as $value) {
                                            $out = $out . TextFormat::AQUA . $value . "\n"; 
                                        }

                                        $sender->sendMessage($out);
                                
                                    break;
                                }
                                default: {
                                    $sender->sendMessage("/er rank <rank id> edit");
                                    $sender->sendMessage("/er rank <rank id> setperm");
                                    $sender->sendMessage("/er rank <rank id> getperms");
                                    $sender->sendMessage("/er rank <rank id> rmperm");
                                    $sender->sendMessage("/er rank <rank id> addinherit");
                                    $sender->sendMessage("/er rank <rank id> getinherit");
                                    $sender->sendMessage("/er rank <rank id> rminherit");
                                    return false;
                                }
                            }
                        } else {
                            $sender->sendMessage("/er rank <rank id> edit");
                            $sender->sendMessage("/er rank <rank id> setperm");
                            $sender->sendMessage("/er rank <rank id> getperms");
                            $sender->sendMessage("/er rank <rank id> rmperm");
                            $sender->sendMessage("/er rank <rank id> addinherit");
                            $sender->sendMessage("/er rank <rank id> getinherit");
                            $sender->sendMessage("/er rank <rank id> rminherit");
                            return false;
                        }
                    }
                } else {
                    $sender->sendMessage("EasyRank\n/er rank <rank_id>\n/rr rank create <rank_id> <display_name>");
                    return false;
                }
                break;
            }

            case "player": {
                if (isset($args[1])) {

                    $player = Main::$instance->getServer()->getPlayerByPrefix($args[1]);

                    if (isset($args[2])) {
                        switch ($args[2]) {
                            case "set": {
                                if (isset($args[3])) {
                                    try {
                                        $rank = Rank::get($args[3]);
                                        $rank->giveToPlayer($player);
                                        $sender->sendMessage($player->getDisplayName() . " is now ranked ". $rank->getDisplayName());
                                        return true;
                                    } catch (InvalidArgumentException $ex) {
                                        $sender->sendMessage("Rank " . $args[3] . "not found!");
                                        return false;
                                    }
                                }
                                break;
                            }
                            default: {
                                $sender->sendMessage("Possible player actions are: set");
                                return false;
                            }
                        }
                    } else {
                        $rank = Rank::getPlayerRank($player);
                        $sender->sendMessage($player->getName() . " is ranked " . $rank->getDisplayName() . " (" . $rank->getId() . "). To edit, use /rr player \"" . $player->getName() . "\" set <rank id>");
                        return true;
                    }

                } else {
                    $sender->sendMessage("EasyRank\n/er player <player>");
                    return false;
                }
                break;
            }

            default: {
                $sender->sendMessage("EasyRank\n/er player <player>");
                return false;
            }
        }
        return false;
    }
}