<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Location;
use App\Core\LocationLevel;
use Illuminate\Console\Command;
use Laravel\Telescope\Telescope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Migrations\MigrateCommand;

/**
 * Information about the download files can be found here
 * http://download.geonames.org/export/dump/readme.txt
 */
class LocationsPopulateCommand extends Command
{
    public const COUNTRIES_URL = 'http://download.geonames.org/export/dump/countryInfo.txt';

    public const ALL_LOCATIONS_URL = 'http://download.geonames.org/export/dump/allCountries.zip';

    public const HIERARCHY_URL = 'http://download.geonames.org/export/dump/hierarchy.zip';

    public const CONTINENTS = [
        [6255146, 'Africa', 'AF'],
        [6255147, 'Asia', 'AS'],
        [6255148, 'Europe', 'EU'],
        [6255149, 'North America', 'NA'],
        [6255151, 'Oceania', 'OC'],
        [6255150, 'South America', 'SA'],
        [6255152, 'Antarctica', 'AN'],
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:populate {--force-download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the locations table with data from GeoNames';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Telescope::stopRecording();
        DB::connection()->unsetEventDispatcher();
        $databaseFile = (new Location)->getConnection()->getDatabaseName();
        $this->info('Clearing locations table');
        if (! file_exists($databaseFile)) {
            touch($databaseFile);
        }
        $this->call(MigrateCommand::class, [
            '--database' => config('mappings.locations.database'),
            '--path' => 'database/locations-migrations',
            '--force' => true,
        ]);

        Location::query()->truncate();

        $this->info('Inserting continents');
        Location::query()->insert(array_map(function ($continent) {
            return [
                'geoname_id' => $continent[0],
                'name' => $continent[1],
                'level' => LocationLevel::CONTINENT->value,
            ];
        }, self::CONTINENTS));

        $continentGeonameIdMap = [];
        foreach (self::CONTINENTS as $continent) {
            $continentGeonameIdMap[$continent[2]] = $continent[0];
        }

        $countryGeonameIdMap = [];

        $insert = [];

        $stream = fopen(self::COUNTRIES_URL, 'r');
        if (! $stream) {
            $this->error('Could not load data from '.self::COUNTRIES_URL);

            return 1;
        }
        while (($line = fgets($stream)) !== false) {
            if (str_starts_with($line, '#')) {
                continue;
            }
            [
                $iso,
                $iso3,
                $isoNumeric,
                $fips,
                $country,
                $capital,
                $area,
                $population,
                $continent,
                $tld,
                $currency,
                $currencyName,
                $phone,
                $postcodeFormat,
                $postcodeRegex,
                $languages,
                $geonameId,
                $neighbours,
                $equivalentFipsCode,
            ] = explode("\t", $line);
            $countryGeonameIdMap[$iso] = $geonameId;
            $insert[] = [
                'geoname_id' => (int) $geonameId,
                'level' => LocationLevel::COUNTRY->value,
                'geoname_parent_id' => $continentGeonameIdMap[$continent],
                'population' => (int) $population,
                'name' => $country,
            ];
        }

        $this->info('Inserting countries');
        Location::query()->insert($insert);

        $locationDataDirectory = storage_path('app/location_data');
        $locationDataZip = storage_path('app/location_data.zip');
        $hierarchyDataDirectory = storage_path('app/hierarchy');
        $hierarchyDataZip = storage_path('app/hierarchy.zip');
        if ($this->option('force-download') || ! file_exists($locationDataDirectory.'/allCountries.txt')) {
            $this->info('Downloading all region data (this may take a few minutes)');
            file_put_contents($locationDataZip, fopen(self::ALL_LOCATIONS_URL, 'r'));
            $this->info('Downloading hierarchy data');
            file_put_contents($hierarchyDataZip, fopen(self::HIERARCHY_URL, 'r'));
            $this->info('Extracting data');

            $zip = new \ZipArchive;
            $zip->open($locationDataZip);
            $zip->extractTo($locationDataDirectory);
            $zip->close();
            unlink($locationDataZip);

            $zip->open($hierarchyDataZip);
            $zip->extractTo($hierarchyDataDirectory);
            $zip->close();
            unlink($hierarchyDataZip);
        }

        $hierarchyMap = [];
        $stream = fopen($hierarchyDataDirectory.'/hierarchy.txt', 'r');
        if (! $stream) {
            $this->error('Could not load data from file '.$hierarchyDataDirectory.'/hierarchy.txt');

            return 1;
        }
        while (($line = fgets($stream)) !== false) {
            [$parent, $child] = explode("\t", $line);
            $hierarchyMap[$child] = $parent;
        }

        $result = exec('wc -l '.$locationDataDirectory.'/allCountries.txt');
        $lineCount = (int) explode(' ', $result ?: ' 0')[1];

        $this->info('Inserting location data');

        $bar = $this->output->createProgressBar($lineCount);
        $bar->start();

        $stream = fopen($locationDataDirectory.'/allCountries.txt', 'r');
        if (! $stream) {
            $this->error('Could not load data from file '.$locationDataDirectory.'/allCountries.txt');

            return 1;
        }
        $inserts = [];
        while (($line = fgets($stream)) !== false) {
            $bar->advance();
            [
                $geonameId,
                $name,
                $asciiName,
                $alternateNames,
                $latitude,
                $longitude,
                $featureClass,
                $featureCode,
                $countryCode,
                $cc2,
                $a1Code,
                $a2Code,
                $a3Code,
                $a4Code,
                $population,
                $elevation,
                $dem,
                $timezone,
                $modificationDate,
            ] = explode("\t", $line);

            if (\in_array($featureCode, ['ADM1', 'ADM2'], true)) {
                if ($countryCode === 'US' && $featureCode === 'ADM2') {
                    continue;
                }
                if (\in_array($geonameId, $countryGeonameIdMap, true)) {
                    continue;
                }
                $level = LocationLevel::STATE;
            } elseif (preg_match('/PPL(C|A2?)?/', $featureCode) && ((int) $population) > 15000) {
                $level = LocationLevel::CITY;
                if ($countryCode === 'US') {
                    $name .= ", $a1Code";
                }
            } else {
                continue;
            }
            $inserts[] = [
                'geoname_id' => $geonameId,
                'level' => $level,
                'geoname_parent_id' => $hierarchyMap[$geonameId] ?? $countryGeonameIdMap[$countryCode] ?? null,
                'country_geoname_id' => $countryGeonameIdMap[$countryCode],
                'country_code' => $countryCode,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'population' => $population,
                'name' => $name,
            ];

            if (\count($inserts) > 100) {
                Location::query()->insert($inserts);
                $inserts = [];
            }
        }

        Location::query()->insert($inserts);

        $bar->finish();
        $this->info("\n");

        $this->info('Location data has been populated');

        return 0;
    }
}
