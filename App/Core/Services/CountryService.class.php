<?php

declare(strict_types=1);

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

class CountryService
{
    private const string GEOIP_PATH = STORAGE . 'database' . DS . 'geoip' . DS . 'GeoLite2-Country.mmdb';

    private ?Reader $geoIpReader = null;
    private array $countries = [];
    private array $states = [];

    public function __construct(
        private CountryModel $countryModel,
        private EntityFactory $entityFactory,
        private ?string $geoIpDbPath = null,
    ) {
        $path = $geoIpDbPath ?? self::GEOIP_PATH;
        if (file_exists($path)) {
            try {
                $this->geoIpReader = new Reader($path);
            } catch (InvalidDatabaseException $e) {
                // Log error: Invalid GeoIP database
                error_log('Invalid GeoIP database: ' . $e->getMessage());
            }
        }
    }

    /**
     * @return Country[]
     */
    public function getAllCountries(): array
    {
        // Check cache first
        if (!empty($this->countries)) {
            return $this->countries;
        }

        // Try to get from database first
        $result = $this->countryModel->all(['is_active' => true]);
        if ($result->isSuccess()) {
            $dbCountries = $result->asArray();
            if (!empty($dbCountries)) {
                $this->countries = $dbCountries;
                return $this->countries;
            }
        }

        // If database is empty, generate from GeoIP or fallback
        $this->countries = $this->getCountriesFromGeoIp();
        return $this->countries;
    }

    public function getCountryByCode(string $isoCode): ?Country
    {
        $countries = $this->getAllCountries();

        foreach ($countries as $country) {
            if (is_array($country) && isset($country['iso_code']) && $country['iso_code'] === $isoCode) {
                // If it's an array, convert to Country object or return as is
                return $this->arrayToCountry($country);
            }
            if ($country instanceof Country && $country->getIsoCode() === $isoCode) {
                return $country;
            }
        }

        return null;
    }

    public function getCountryById(int $id): ?Country
    {
        $countries = $this->getAllCountries();

        foreach ($countries as $country) {
            if ($country instanceof Country && $country->getId() === $id) {
                return $country;
            }
        }

        return null;
    }

    public function getCountryByIp(string $ip): ?Country
    {
        if ($this->geoIpReader === null) {
            return null;
        }

        try {
            $record = $this->geoIpReader->country($ip);
            $isoCode = $record->country->isoCode;

            if ($isoCode === null) {
                return null;
            }

            return $this->getCountryByCode($isoCode);
        } catch (AddressNotFoundException $e) {
            // IP not found in database
            return null;
        } catch (Exception $e) {
            error_log('GeoIP lookup error: ' . $e->getMessage());
            return null;
        }
    }

    public function getStatesForCountry(string $countryCode): array
    {
        // Check cache first
        if (isset($this->states[$countryCode])) {
            return $this->states[$countryCode];
        }

        $states = $this->getDefaultStatesForCountry($countryCode);
        $this->states[$countryCode] = $states;

        return $states;
    }

    public function getCountryName(string $isoCode): string
    {
        $country = $this->getCountryByCode($isoCode);
        if ($country instanceof Country) {
            return $country->getOfficialName();
        }
        if (is_array($country) && isset($country['official_name'])) {
            return $country['official_name'];
        }
        return $isoCode;
    }

    public function getCountryPhonePrefix(string $isoCode): ?string
    {
        $country = $this->getCountryByCode($isoCode);
        if ($country instanceof Country) {
            return $country->getPhonePrefix();
        }
        return null;
    }

    public function isPostalCodeRequired(string $isoCode): bool
    {
        $country = $this->getCountryByCode($isoCode);
        if ($country instanceof Country) {
            return $country->isPostalCodeRequired();
        }
        return true;
    }

    public function isStateRequired(string $isoCode): bool
    {
        $country = $this->getCountryByCode($isoCode);
        if ($country instanceof Country) {
            return $country->isStateRequired();
        }
        return $isoCode === 'US' || $isoCode === 'CA';
    }

    public function getCountryOptions(): array
    {
        $options = ['' => 'Select Country'];
        $countries = $this->getAllCountries();

        foreach ($countries as $country) {
            if ($country instanceof Country) {
                $options[$country->getIsoCode()] = $country->getOfficialName();
            } elseif (is_array($country) && isset($country['iso_code'])) {
                $options[$country['iso_code']] = $country['official_name'];
            }
        }

        return $options;
    }

    /**
     * Get countries from GeoIP database or fallback to hardcoded list.
     */
    private function getCountriesFromGeoIp(): array
    {
        // If GeoIP reader is available, try to extract country list
        if ($this->geoIpReader !== null) {
            try {
                // GeoIP doesn't provide a list of all countries directly
                // So we use a hardcoded list of common countries
                return $this->getFallbackCountries();
            } catch (Exception $e) {
                error_log('Error reading GeoIP: ' . $e->getMessage());
            }
        }

        // Fallback to hardcoded list
        return $this->getFallbackCountries();
    }

    /**
     * Fallback countries when database is empty.
     */
    private function getFallbackCountries(): array
    {
        $countries = [];

        // Common countries with their ISO codes
        $countryData = [
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BR' => 'Brazil',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo (DRC)',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'SZ' => 'Eswatini',
            'ET' => 'Ethiopia',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GR' => 'Greece',
            'GD' => 'Grenada',
            'GT' => 'Guatemala',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HN' => 'Honduras',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KP' => 'North Korea',
            'KR' => 'South Korea',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'MK' => 'North Macedonia',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'QA' => 'Qatar',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'SS' => 'South Sudan',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ];

        // Create Country objects from the data
        foreach ($countryData as $isoCode => $name) {
            // If we can't create proper Country objects, return the data as array
            // that can be used directly for options
            $countries[] = [
                'iso_code' => $isoCode,
                'official_name' => $name,
                'iso3_code' => '',
                'numeric_code' => '',
                'postal_code_required' => true,
                'state_required' => ($isoCode === 'US' || $isoCode === 'CA'),
                'is_active' => true,
            ];
        }

        return $countries;
    }

    private function arrayToCountry(array $data): ?Country
    {
        // If we have a Country class, create it
        if (class_exists(Country::class)) {
            try {
                /** @var Country */
                $country = $this->entityFactory->create(Country::class);
                // Set properties if they exist
                if (isset($data['iso_code'])) {
                    $country->setIsoCode($data['iso_code']);
                }
                if (isset($data['official_name'])) {
                    $country->setOfficialName($data['official_name']);
                }
                if (isset($data['iso3_code'])) {
                    $country->setIso3Code($data['iso3_code']);
                }
                if (isset($data['numeric_code'])) {
                    $country->setNumericCode($data['numeric_code']);
                }
                if (isset($data['postal_code_required'])) {
                    $country->setPostalCodeRequired($data['postal_code_required']);
                }
                if (isset($data['state_required'])) {
                    $country->setStateRequired($data['state_required']);
                }
                return $country;
            } catch (Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function getDefaultStatesForCountry(string $countryCode): array
    {
        return match ($countryCode) {
            'US' => [
                'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona',
                'AR' => 'Arkansas', 'CA' => 'California', 'CO' => 'Colorado',
                'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida',
                'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
                'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
                'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
                'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts',
                'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
                'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska',
                'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
                'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina',
                'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
                'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island',
                'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee',
                'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
                'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
                'WI' => 'Wisconsin', 'WY' => 'Wyoming',
            ],
            'CA' => [
                'AB' => 'Alberta', 'BC' => 'British Columbia', 'MB' => 'Manitoba',
                'NB' => 'New Brunswick', 'NL' => 'Newfoundland and Labrador',
                'NS' => 'Nova Scotia', 'ON' => 'Ontario', 'PE' => 'Prince Edward Island',
                'QC' => 'Quebec', 'SK' => 'Saskatchewan', 'NT' => 'Northwest Territories',
                'NU' => 'Nunavut', 'YT' => 'Yukon',
            ],
            'GB' => [
                'ENG' => 'England', 'SCT' => 'Scotland', 'WLS' => 'Wales',
                'NIR' => 'Northern Ireland',
            ],
            'AU' => [
                'NSW' => 'New South Wales', 'VIC' => 'Victoria', 'QLD' => 'Queensland',
                'WA' => 'Western Australia', 'SA' => 'South Australia',
                'TAS' => 'Tasmania', 'ACT' => 'Australian Capital Territory',
                'NT' => 'Northern Territory',
            ],
            'DE' => [
                'BW' => 'Baden-Württemberg', 'BY' => 'Bavaria', 'BE' => 'Berlin',
                'BB' => 'Brandenburg', 'HB' => 'Bremen', 'HH' => 'Hamburg',
                'HE' => 'Hesse', 'MV' => 'Mecklenburg-Vorpommern', 'NI' => 'Lower Saxony',
                'NW' => 'North Rhine-Westphalia', 'RP' => 'Rhineland-Palatinate',
                'SL' => 'Saarland', 'SN' => 'Saxony', 'ST' => 'Saxony-Anhalt',
                'SH' => 'Schleswig-Holstein', 'TH' => 'Thuringia',
            ],
            default => [],
        };
    }
}