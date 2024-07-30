<?php

namespace App\Factory;

use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Data\GeoCoordinates;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\DataObject\Team;

class TeamFactory
{
    public function create(
        string $name,
        string $description,
        Image $image,
        Image $teamPhoto,
        string $coach,
        string $location,
        string $city,
        GeoCoordinates $coordinates,
        int $foundedYear,
        Folder $parent,
    ): Team {
        $team = new Team();
        $team->setKey(Service::getValidKey($name, 'object'));
        $team->setParent($parent);
        $team->setName($name);
        $team->setDescription($description);
        $team->setImage($image);
        $team->setTeamPhoto($teamPhoto);
        $team->setCoach($coach);
        $team->setLocation($location);
        $team->setCity($city);
        $team->setCoordinates($coordinates);
        $team->setFoundedYear($foundedYear);

        return $team;
    }
}
