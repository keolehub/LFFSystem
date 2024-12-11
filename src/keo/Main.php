<?php

namespace keo;

use pocketmine\block\VanillaBlocks;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TE;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\transaction\InvMenuTransaction;
use keo\CooldownLFF;

class Main extends PluginBase {

    private $playerClasses = [];
    private $cooldownLff; 

    public function onEnable() : void {
        $this->cooldownLff = new CooldownLFF();        
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
    }

    public function onDisable() : void {
    }

    private function getMenuItem($type, $name, $lore): Item {
        switch ($type) {
            case 'BARD':
                return VanillaItems::BLAZE_POWDER()->setCustomName("§r§l§6{$name}")->setLore($lore);
            case 'ARCHER':
                return VanillaItems::BOW()->setCustomName("§r§l§6{$name}")->setLore($lore);
            case 'DIAMOND':
                return VanillaItems::DIAMOND_CHESTPLATE()->setCustomName("§r§l§b{$name}")->setLore($lore);
            case 'ROGUE':
                return VanillaItems::GOLDEN_SWORD()->setCustomName("§r§l§f{$name}")->setLore($lore);
            case 'MAGUE':
                return VanillaItems::GOLDEN_HELMET()->setCustomName("§r§l§2{$name}")->setLore($lore);
            case 'NINJA':
                return VanillaItems::STONE_SWORD()->setCustomName("§r§l§0{$name}")->setLore($lore);
            case 'BACK':
                $item = VanillaItems::ARROW(); 
                $item->setCustomName("§r§l§7{$name}")->setLore($lore);
                return $item;
            default:
                return VanillaBlocks::STAINED_GLASS()->asItem()->setCustomName("§r§7  ")->setLore($lore);
        }
    }

    public function openMenu(Player $player): void {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName("§l§bLFF §5(FortuneMC)");
        $inventory = $menu->getInventory();
        $inventory->setItem(11, $this->getMenuItem('BARD', "§aBARD §7(CLASS)", ["§fThis Class Is Used To Grant \nEffects To Your Team"]));
        $inventory->setItem(13, $this->getMenuItem('ARCHER', "§6ARCHER §7(CLASS)", ["§fWhen Tagging With Your Bow\nThe Opponent Will Take 20% More Damage\nFrom You And Your Team"]));
        $inventory->setItem(15, $this->getMenuItem('DIAMOND', "§bDIAMOND §7(CLASS)", ["§fThis class is used to \ndefend and raid"]));
        $inventory->setItem(29, $this->getMenuItem('ROGUE', "§fROGUE §7(CLASS)", ["§fThis Class By Hitting With The Golden Sword\nYou Will Take Hearts From The Rival"]));
        $inventory->setItem(31, $this->getMenuItem('MAGUE', "§2MAGE §7(CLASS)", ["§f This Class Serves Very \nWell To Defend"]));
        $inventory->setItem(33, $this->getMenuItem('NINJA', "§0NINJA §7(CLASS)", ["§fThis class is used for stealth lovers and trap setters."]));
        $inventory->setItem(49, $this->getMenuItem('BACK', "§7BACK", ["§fExit LFF Menu"]));

        $menu->send($player);
        for ($i = 0; $i <= 53; $i++) {
            if (!in_array($i, [11, 13, 15, 29, 31, 33, 49])) {
                $inventory->setItem($i, $this->getMenuItem('EMPTY', '§r§7  ', []));
            }
        }

        $menu->setListener(function(InvMenuTransaction $transaction) : InvMenuTransactionResult {
            $player = $transaction->getPlayer();
            $itemClicked = $transaction->getItemClicked();

            $itemTypeId = $itemClicked->getTypeId();
            $classes = [
                VanillaItems::BLAZE_POWDER()->getTypeId() => 'BARD',
                VanillaItems::BOW()->getTypeId() => 'ARCHER',
                VanillaItems::DIAMOND_CHESTPLATE()->getTypeId() => 'DIAMOND',
                VanillaItems::GOLDEN_SWORD()->getTypeId() => 'ROGUE',
                VanillaItems::GOLDEN_HELMET()->getTypeId() => 'MAGUE',
                VanillaItems::STONE_SWORD()->getTypeId() => 'NINJA',
                VanillaItems::ARROW()->getTypeId() => 'BACK',
            ];

            if (isset($classes[$itemTypeId])) {
                $class = $classes[$itemTypeId];

                if (!isset($this->playerClasses[$player->getName()])) {
                    $this->playerClasses[$player->getName()] = [];
                }

                if ($class !== 'BACK' && !in_array($class, $this->playerClasses[$player->getName()])) {
                    $this->playerClasses[$player->getName()][] = $class;
                }

                if ($class === 'BACK') {
                    $classColors = [
                        'BARD' => '§e',
                        'ARCHER' => '§6',
                        'DIAMOND' => '§b',
                        'ROGUE' => '§8',
                        'MAGUE' => '§2',
                        'NINJA' => '§0'
                    ];

                    $chosenClasses = [];
                    foreach ($this->playerClasses[$player->getName()] as $class) {
                        if (isset($classColors[$class])) {
                            $formattedClass = ucfirst(strtolower($class));
                            $chosenClasses[] = $classColors[$class] . $formattedClass;
                        }
                    }

                    $chosenClassesString = implode(", ", $chosenClasses);

                    $this->getServer()->broadcastMessage("§7------------------------------------------");
                    $this->getServer()->broadcastMessage("§r§2{$player->getName()} §7is looking for a Faction!");
                    $this->getServer()->broadcastMessage("§r§2Class: §r{$chosenClassesString}");
                    $this->getServer()->broadcastMessage("§7------------------------------------------");

                    $this->playerClasses[$player->getName()] = [];
                    $player->removeCurrentWindow();
                }

                return $transaction->discard();
            }

            $player->sendMessage(TE::RED . "You Can't Use That Item!");
            return $transaction->discard();
        });

        $menu->send($player);
    }

    public function onCommand(CommandSender $sender, Command $command, String $label, Array $args) : bool {
        if ($command->getName() == "lff") {
            $playerName = $sender->getName();
            if ($this->cooldownLff->isOnCooldown($playerName)) {
                $remainingTime = $this->cooldownLff->getRemainingCooldown($playerName);
                $sender->sendMessage(TE::RED . "You must wait $remainingTime more seconds before using this command again.");
                return true;
            }
            $this->cooldownLff->setCooldown($playerName, 60);
            $sender->sendMessage(TE::RED . "Choose Your Class To Be Recruited");
            if ($sender instanceof Player) {
                $this->openMenu($sender);
            }
        }
        return true;
    }
}
