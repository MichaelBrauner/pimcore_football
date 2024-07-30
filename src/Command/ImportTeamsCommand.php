<?php

namespace App\Command;

use App\Factory\PlayerFactory;
use App\Factory\TeamFactory;
use App\Service\Asset\FolderManager as AssetFolderManager;
use App\Service\Document\FolderManager;
use App\Service\football\SpreadSheetReader;
use App\Service\ImageDownloader;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Data\GeoCoordinates;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Player;
use Pimcore\Model\DataObject\Team;
use Pimcore\Model\Element\Service;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Symfony\Component\String\u;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'app:import-teams',
    description: 'Imports team data from an Excel file.',
)]
class ImportTeamsCommand extends Command
{
    public function __construct(
        private readonly PlayerFactory $playerFactory,
        private readonly TeamFactory $teamFactory,
        private readonly ImageDownloader $imageDownloader,
        private readonly FolderManager $folderManager,
        private readonly AssetFolderManager $assetFolderManager,
        private readonly SpreadSheetReader $spreadSheetReader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The Excel file to import. Relative to the project root.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        Assert::string($filePath, 'The file path must be a string.');

        $this->deleteExistingObjectsAndFolders();

        foreach ($this->spreadSheetReader->read($filePath) as $teamData) {
            $this->createTeam(...$teamData);
        }

        $io->success('Nice! The football teams have been imported successfully.');

        return Command::SUCCESS;
    }

    private function deleteExistingObjectsAndFolders(): void
    {
        $this->folderManager->deleteFolderIfExists('/football/players');
        $this->folderManager->deleteFolderIfExists('/football/teams');
        $this->folderManager->deleteFolderIfExists('/football');

        $this->assetFolderManager->deleteFolderIfExists('/football/teams');
    }

    private function createTeam(
        string $teamName,
        string $description,
        string $logoUrl,
        string $teamPhotoUrl,
        string $coach,
        string $location,
        string $city,
        string $coordinates,
        int $foundedYear,
        string $playersData,
    ): void {
        $teamsFolder = $this->createTeamsFolder();
        $teamsAssetsFolder = $this->createTeamAssetsFolder();
        $logo = $this->imageDownloader->downloadAndSaveImageAsAsset(
            url: $logoUrl,
            parent: $teamsAssetsFolder,
            filename: u($teamName)->snake()->toString() . '_logo',
        );
        $teamPhoto = $this->imageDownloader->downloadAndSaveImageAsAsset(
            url: $teamPhotoUrl,
            parent: $teamsAssetsFolder,
            filename: u($teamName)->snake()->toString() . '_team_photo',
        );
        $geoCoordinates = $this->createGeoCoordinates($coordinates);

        $team = $this->teamFactory->create(
            name: $teamName,
            description: $description,
            image: $logo,
            teamPhoto: $teamPhoto,
            coach: $coach,
            location: $location,
            city: $city,
            coordinates: $geoCoordinates,
            foundedYear: $foundedYear,
            parent: $teamsFolder,
        );

        $team->setPublished(true);
        $playersFolder = $this->createPlayersFolder();

        $players = $this->createPlayers(
            playersData: $playersData,
            team: $team,
            playersFolder: $playersFolder,
        );

        $team->setPlayers($players);
        $team->save();
    }

    private function createTeamsFolder(): Folder
    {
        $footballFolder = $this->folderManager->getOrCreateFolder('/football', 'football');

        return $this->folderManager->getOrCreateFolder(
            path: '/football/teams',
            key: Service::getValidKey('teams', 'folder'),
            parentId: $footballFolder->getId(),
        );
    }

    private function createTeamAssetsFolder(): Asset\Folder
    {
        $assetsFolder = $this->assetFolderManager->getOrCreateFolder(
            path: '/football',
            key: 'football',
        );

        return $this->assetFolderManager->getOrCreateFolder(
            path: '/football/teams',
            key: 'teams',
            parentId: $assetsFolder->getId(),
        );
    }

    private function createGeoCoordinates(string $coordinates): GeoCoordinates
    {
        $coordinatesArray = explode(',', $coordinates);
        $latitude = $coordinatesArray[0];
        $longitude = $coordinatesArray[1];

        return (new GeoCoordinates())
            ->setLatitude((float)$latitude)
            ->setLongitude((float)$longitude);
    }

    private function createPlayersFolder(): Folder
    {
        $footballFolder = Folder::getByPath('/football');
        Assert::notNull($footballFolder, 'The football folder must exist.');

        return $this->folderManager->getOrCreateFolder(
            path: '/football/players',
            key: Service::getValidKey('players', 'folder'),
            parentId: $footballFolder->getId(),
        );
    }

    /**
     * @return Player[]
     */
    private function createPlayers(string $playersData, Team $team, Folder $playersFolder): array
    {
        $playersData = explode(';', $playersData);

        return array_map(function ($playerData) use ($playersFolder) {
            list($playerName, $number, $age, $position) = explode(',', $playerData);

            $player = $this->playerFactory->create(
                name: $playerName,
                number: (int)$number,
                age: (int)$age,
                position: $position,
                parent: $playersFolder,
            );

            $player->setPublished(true);
            $player->save();

            return $player;
        }, $playersData);
    }
}
