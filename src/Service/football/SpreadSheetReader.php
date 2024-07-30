<?php

namespace App\Service\football;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SpreadSheetReader
{
    /**
     * @var array<string, string>
     */
    const array columns = [
        'teamName' => 'A',
        'description' => 'B',
        'logoUrl' => 'C',
        'teamPhotoUrl' => 'D',
        'coach' => 'E',
        'playersData' => 'F',
        'location' => 'G',
        'city' => 'H',
        'coordinates' => 'I',
        'foundedYear' => 'J',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%/')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return list<array{
     *      teamName: string,
     *      description: string,
     *      logoUrl: string,
     *      teamPhotoUrl: string,
     *      coach: string,
     *      playersData: string,
     *      location: string,
     *      city: string,
     *      coordinates: string,
     *      foundedYear: int
     * }>
     */
    public function read(string $filePath): array
    {
        $spreadsheet = IOFactory::load($this->projectDir . $filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $teams = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex = $row->getRowIndex();
            if ($rowIndex == 1) {
                continue;
            }

            $data = array_map(
                callback: fn ($column): string => (string) $sheet->getCell($column . $rowIndex)->getValue(),
                array: self::columns,
            );

            $teams[] = [
                'teamName' => $data['teamName'],
                'description' => $data['description'],
                'logoUrl' => $data['logoUrl'],
                'teamPhotoUrl' => $data['teamPhotoUrl'],
                'coach' => $data['coach'],
                'playersData' => $data['playersData'],
                'location' => $data['location'],
                'city' => $data['city'],
                'coordinates' => $data['coordinates'],
                'foundedYear' => (int) $data['foundedYear'],
            ];
        }

        return $teams;
    }
}
