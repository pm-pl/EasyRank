<?php

declare(strict_types=1);

namespace Valea\EasyRank;

use Valea\EasyRank\storage\Provider;
use Exception;
use Valea\EasyRank\api\Rank;
use Valea\EasyRank\listeners\Join;
use Valea\EasyRank\listeners\Leave;
use Valea\EasyRank\storage\providers\ConfigProvider;
use pocketmine\plugin\PluginBase;
use Valea\EasyRank\commands\EasyRank;
use Valea\EasyRank\listeners\Chat;
use Valea\EasyRank\tasks\NameTags;
use pocketmine\Server;
use pocketmine\utils\Config;

class Main extends PluginBase{

    public static Main $instance;

    public static Config $config;

    public static array $attachments;

    public static Server $server;

    public static Provider $storageProvider;

    public function onEnable(): void
    {
        self::$instance = $this;

        $this->getServer()->getPluginManager()->registerEvents(new Leave(), $this);
        $this->getServer()->getPluginManager()->registerEvents(new Join(), $this);
        
        self::$config = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "storage" => "config",
            "edit_name_tags" => true,
            "format_chat" => true
        ]);

        if (self::$config->get("format_chat", true)) {
            $this->getServer()->getPluginManager()->registerEvents(new Chat(), $this);
        }

        if (self::$config->get("edit_name_tags", true)) {
            $this->getScheduler()->scheduleRepeatingTask(new NameTags(), 40);
        }

        
        $this->setProvider(new ConfigProvider());

        $cmd = $this->getCommand("er");
        $cmd->{"setExecutor"}(new EasyRank());
    }

    public function setProvider(Provider $provider) {
        self::$storageProvider = $provider;
        Rank::$provider = $provider;
    }

    public function getProviderAbstractClass() {
        return Provider::class;
    }

    public function getRankClass() {
        return Provider::class;
    }

}
