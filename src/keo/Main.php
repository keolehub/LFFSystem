<?php

namespace keo;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TE;
use keo\CooldownLFF;
use keo\menu\LFFMenu;

class Main extends PluginBase {

    private $playerClasses = [];
    private $cooldownLff;

    public function onEnable() : void {
        $this->cooldownLff = new CooldownLFF();
        LFFMenu::initialize($this);
    }

    public function onDisable() : void {
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool {
    if ($command->getName() === "lff") {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TE::RED . "This command can only be used by players.");
            return false;
        }
        $playerName = $sender->getName();
        if ($this->cooldownLff->isOnCooldown($playerName)) {
            $remainingTime = $this->cooldownLff->getRemainingCooldown($playerName);
            $sender->sendMessage(TE::RED . "You must wait $remainingTime more seconds before using this command again.");
            return true;
        }
        $this->cooldownLff->setCooldown($playerName, 60);
        $sender->sendMessage(TE::RED . "Choose Your Class To Be Recruited");
        LFFMenu::openMenu($sender, $this->playerClasses);
        return true;
    }
    return false;
    }
}
