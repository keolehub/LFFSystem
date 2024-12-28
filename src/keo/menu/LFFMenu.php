<?php

namespace keo\menu;

use keo\Main;
use pocketmine\item\VanillaItems;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TE;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\transaction\InvMenuTransaction;

class LFFMenu {

    private static $plugin;

    public static function initialize(Main $plugin): void {
        self::$plugin = $plugin;
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($plugin);
        }
    }

    private static function getMenuItem($type, $name, $lore) {
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

    public static function openMenu(Player $player, array &$playerClasses): void {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName("§l§bLFF §5(FortuneMC)");
        $inventory = $menu->getInventory();
        $inventory->setItem(11, self::getMenuItem('BARD', "§aBARD §7(CLASS)", ["§fThis Class Is Used To Grant \nEffects To Your Team"]));
        $inventory->setItem(13, self::getMenuItem('ARCHER', "§6ARCHER §7(CLASS)", ["§fWhen Tagging With Your Bow\nThe Opponent Will Take 20% More Damage\nFrom You And Your Team"]));
        $inventory->setItem(15, self::getMenuItem('DIAMOND', "§bDIAMOND §7(CLASS)", ["§fThis class is used to \ndefend and raid"]));
        $inventory->setItem(29, self::getMenuItem('ROGUE', "§fROGUE §7(CLASS)", ["§fThis Class By Hitting With The Golden Sword\nYou Will Take Hearts From The Rival"]));
        $inventory->setItem(31, self::getMenuItem('MAGUE', "§2MAGE §7(CLASS)", ["§f This Class Serves Very \nWell To Defend"]));
        $inventory->setItem(33, self::getMenuItem('NINJA', "§0NINJA §7(CLASS)", ["§fThis class is used for stealth lovers and trap setters."]));
        $inventory->setItem(49, self::getMenuItem('BACK', "§7BACK", ["§fExit LFF Menu"]));

        for ($i = 0; $i <= 53; $i++) {
            if (!in_array($i, [11, 13, 15, 29, 31, 33, 49])) {
                $inventory->setItem($i, self::getMenuItem('EMPTY', '§r§7  ', []));
            }
        }

        $menu->setListener(function(InvMenuTransaction $transaction) use (&$playerClasses): InvMenuTransactionResult {
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

                if (!isset($playerClasses[$player->getName()])) {
                    $playerClasses[$player->getName()] = [];
                }

                if ($class !== 'BACK' && !in_array($class, $playerClasses[$player->getName()])) {
                    $playerClasses[$player->getName()][] = $class;
                }

                if ($class === 'BACK') {
                    $classColors = [
                        'BARD' => '§e',
                        'ARCHER' => '§6',
                        'DIAMOND' => '§b',
                        'ROGUE' => '§8',
                        'MAGUE' => '§2',
                        'NINJA' => '§0',
                    ];

                    $chosenClasses = [];
                    foreach ($playerClasses[$player->getName()] as $class) {
                        if (isset($classColors[$class])) {
                            $formattedClass = ucfirst(strtolower($class));
                            $chosenClasses[] = $classColors[$class] . $formattedClass;
                        }
                    }

                    $chosenClassesString = implode(", ", $chosenClasses);

                    self::$plugin->getServer()->broadcastMessage("§7------------------------------------------");
                    self::$plugin->getServer()->broadcastMessage("§r§2{$player->getName()} §7is looking for a Faction!");
                    self::$plugin->getServer()->broadcastMessage("§r§2Class: §r{$chosenClassesString}");
                    self::$plugin->getServer()->broadcastMessage("§7------------------------------------------");

                    $playerClasses[$player->getName()] = [];
                    $player->removeCurrentWindow();
                }

                return $transaction->discard();
            }

            $player->sendMessage(TE::RED . "You Can't Use That Item!");
            return $transaction->discard();
        });

        $menu->send($player);
    }
}

