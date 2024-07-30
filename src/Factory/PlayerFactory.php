<?php

namespace App\Factory;

use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Player;
use Pimcore\Model\DataObject\Service;

class PlayerFactory
{
    public function create(
        string $name,
        int $number,
        int $age,
        string $position,
        Folder $parent,
    ): Player {
        $player = new Player();
        $player->setKey(Service::getValidKey($name, 'object'));
        $player->setName($name);
        $player->setNumber($number);
        $player->setAge($age);
        $player->setPosition($position);
        $player->setParent($parent);

        return $player;
    }
}
