<?

declare(strict_types=1);

namespace Valea\EasyRank\api;

use Valea\EasyRank\storage\Provider;
use InvalidArgumentException;
use Valea\EasyRank\Main;
use pocketmine\player\Player;

class Rank {
    
    public static Provider $provider;

    private string $id;

    public function __construct(string $id, array $permissions, array $inherit)
    {
        $this->id = $id;

        $futurePermissions = [];
        
        foreach ($inherit as $groupId) {
            foreach (self::$provider->getPermissionsFromId($groupId) as $node => $value) {
                $futurePermissions[$node] = $value;
            }
        }

        foreach ($permissions as $node => $value) {
            $futurePermissions[$node] = $value;
        }

        $this->setPermissions($futurePermissions, false);
    }

    public function getId(): string {
        return $this->id;
    }

    public static function get(string $id) : Rank {
        $ranks = self::$provider->getRanks();
        if (!isset($ranks[$id])) {
            throw new InvalidArgumentException("Rank " . $id . " not found!");
        }
        return $ranks[$id];
    }

    public static function create(string $id, string $displayName, array $permissions = [], ?string $chatFormat = null, ?string $nameTagFormat = null, array $inherit = []): Rank {
        return self::$provider->createRank($id, $displayName, $permissions, $chatFormat, $nameTagFormat, $inherit);
    }

    public static function getPlayerRank(Player $player)
    {
        return self::$provider->getPlayerRank($player);
    }

    public function addPermissions(Player $player) {
        if (isset(Main::$attachments[$player->getUniqueId()->toString()])) {
            $attachment = Main::$attachments[$player->getUniqueId()->toString()];
        } else {
            $attachment = $player->addAttachment(Main::$instance);
        }
        $attachment->clearPermissions();
        foreach ($this->getPermissions() as $node => $value) {
            $attachment->setPermission($node, $value);
        }
        Main::$attachments[$player->getUniqueId()->toString()] = $attachment;
    }

    public function getDisplayName(): string {
        return self::$provider->getField($this, "displayName");
    }

    public function getPermissions(): array {
        return self::$provider->getField($this, "permissions");
    }

    public function getInherit(): array {
        return self::$provider->getField($this, "inherit");
    }

    public function getChatFormat(): string {
        return self::$provider->getField($this, "chatFormat");
    }

    public function getNameTagFormat(): string {
        return self::$provider->getField($this, "nameTagFormat");
    }

    public function delete() {
        return self::$provider->deleteRank($this);
    }

    public function giveToPlayer(Player $player) {
        self::$provider->setPlayerRank($player, $this);
        $player->setNameTag(
            str_replace("%name%", $player->getDisplayName(), $this->getNameTagFormat())
        );
    }

    public function setDisplayName(string $new) {
        self::$provider->setField($this, "displayName", $new);
    }

    public function setChatFormat(string $new) {
        self::$provider->setField($this, "chatFormat", $new);
    }

    public function setNameTagFormat(string $new) {
        self::$provider->setField($this, "nameTagFormat", $new);
    }

    public function setInherit(array $new) {
        self::$provider->setField($this, "inherit", $new);
    }

    public function setPermissions(array $new, bool $doAdd = true) {
        self::$provider->setField($this, "permissions", $new);
        if ($doAdd) {
            foreach (Main::$instance->getServer()->getOnlinePlayers() as $player) {
                if (self::getPlayerRank($player)->getId() === $this->getId()) {
                    $this->addPermissions($player);
                }
            }
        }
    }

}