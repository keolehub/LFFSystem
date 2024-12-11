<?php

namespace keo;

use pocketmine\utils\TextFormat as TE;

class CooldownLFF {

    private $cooldowns = [];
    public function setCooldown(string $playerName, int $time): void {
        $this->cooldowns[$playerName] = time() + $time;
    }

    public function isOnCooldown(string $playerName): bool {
        if (isset($this->cooldowns[$playerName])) {
            if ($this->cooldowns[$playerName] > time()) {
                return true;
            } else {
                unset($this->cooldowns[$playerName]);
            }
        }
        return false;
    }
    public function getRemainingCooldown(string $playerName): int {
        if (isset($this->cooldowns[$playerName])) {
            $remaining = $this->cooldowns[$playerName] - time();
            return max(0, $remaining);
        }
        return 0;
    }
}
