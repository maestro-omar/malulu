<?php

namespace Database\Seeders\Faker;

use Faker\Provider\Base;
use App\Models\Entities\User;
use App\Models\Catalogs\Role;
use App\Models\Catalogs\SchoolLevel;
use Illuminate\Support\Str;

class UserFaker extends Base
{
    /**
     * DNI ranges and corresponding birth year ranges
     */
    private static array $dniYearRanges = [
        ['dni_min' => 1000000, 'dni_max' => 10000000, 'year_min' => 1920, 'year_max' => 1945],
        ['dni_min' => 10000000, 'dni_max' => 15000000, 'year_min' => 1946, 'year_max' => 1955],
        ['dni_min' => 15000000, 'dni_max' => 20000000, 'year_min' => 1956, 'year_max' => 1965],
        ['dni_min' => 20000000, 'dni_max' => 26000000, 'year_min' => 1966, 'year_max' => 1975],
        ['dni_min' => 26000000, 'dni_max' => 29000000, 'year_min' => 1976, 'year_max' => 1980],
        ['dni_min' => 29000000, 'dni_max' => 32000000, 'year_min' => 1981, 'year_max' => 1985],
        ['dni_min' => 32000000, 'dni_max' => 40000000, 'year_min' => 1986, 'year_max' => 1995],
        ['dni_min' => 40000000, 'dni_max' => 46000000, 'year_min' => 1996, 'year_max' => 2005],
        ['dni_min' => 46000000, 'dni_max' => 51000000, 'year_min' => 2006, 'year_max' => 2010],
        ['dni_min' => 51000000, 'dni_max' => 55000000, 'year_min' => 2011, 'year_max' => 2015],
        ['dni_min' => 55000000, 'dni_max' => 58000000, 'year_min' => 2016, 'year_max' => 2018],
        ['dni_min' => 58000000, 'dni_max' => 62000000, 'year_min' => 2019, 'year_max' => 2021],
        ['dni_min' => 62000000, 'dni_max' => 66000000, 'year_min' => 2022, 'year_max' => 2024],
        ['dni_min' => 66000000, 'dni_max' => 70000000, 'year_min' => 2025, 'year_max' => 2027],
        ['dni_min' => 70000000, 'dni_max' => 75000000, 'year_min' => 2028, 'year_max' => 2030],
    ];
    /**
     * Generate a random gender based on realistic distribution
     */
    public function userGender(): string
    {
        $genderOptions = [
            User::GENDER_MALE,
            User::GENDER_FEMALE,
            User::GENDER_TRANS,
            User::GENDER_FLUID,
            User::GENDER_NOBINARY,
            User::GENDER_OTHER,
        ];

        // 80% masc/fem, 20% other
        return $this->generator->randomElement(
            $this->generator->boolean(80)
                ? [User::GENDER_MALE, User::GENDER_FEMALE]
                : array_slice($genderOptions, 2)
        );
    }

    /**
     * Generate a random locality from San Luis province
     */
    public function locality(): string
    {
        $localities = [
            'San Luis' => 80,
            'Juana Koslay' => 10,
            'La Punta' => 5,
            'Potrero de los Funes' => 3,
            'Villa Mercedes' => 2,
            'El Trapiche' => 2,
            'La Toma' => 2,
            'Merlo' => 1,
            'Concarán' => 1,
            'Quines' => 1,
            'Santa Rosa del Conlara' => 1,
            'Buena Esperanza' => 1,
            'San Francisco del Monte de Oro' => 1,
            'Luján' => 1,
            'Nueva Galia' => 1,
        ];

        return $this->generator->randomElement($this->getWeightedArray($localities));
    }

    /**
     * Generate a random address with street and number
     */
    public function addressStreetAndNumber(): string
    {
        $addresses = [
            'Pringles',
            'Av. Juan D. Perón',
            'Av. Illia',
            'Av. Lafinur',
            'Av. España',
            'La Rioja',
            'Riobamba',
            'Mitre',
            'Av. Justo Daract',
            'Av. Juan B. Justo',
            '25 de Mayo',
            '9 de Julio',
            'San Martín',
            'Rivadavia',
            'Colón'
        ];

        $street = $this->generator->randomElement($addresses);
        $number = $this->generator->numberBetween(100, 9999);

        return $street . ' ' . $number;
    }

    /**
     * Generate a realistic DNI based on birthdate and nationality
     */
    public function dniFromBirthdate($birthdate, $nationality): string
    {
        if ($nationality !== 'Argentina') {
            return (string) $this->generator->numberBetween(91000000, 99999999);
        }

        $birthYear = $birthdate->format('Y');
        [$min, $max] = $this->getDNIRangeForBirthYear($birthYear);

        return (string) $this->generator->numberBetween($min, $max);
    }

    /**
     * Generate appropriate birthdate based on role and school levels
     */
    public function birthdateForRole(string $roleCode, $schoolLevels): \DateTime
    {
        if ($roleCode === Role::STUDENT) {
            if ($schoolLevels->isEmpty()) {
                return $this->generator->dateTimeBetween('-18 years', '-5 years');
            }
            $schoolLevel = $schoolLevels->random();
            if ($schoolLevel->code === SchoolLevel::KINDER) {
                return $this->generator->dateTimeBetween('-5 years', '-2 years');
            } elseif ($schoolLevel->code === SchoolLevel::PRIMARY) {
                return $this->generator->dateTimeBetween('-11 years', '-6 years');
            } elseif ($schoolLevel->code === SchoolLevel::SECONDARY) {
                return $this->generator->dateTimeBetween('-18 years', '-12 years');
            }
            throw new \Exception('Unexpected school level: ' . $schoolLevel->code);
        }

        return match ($roleCode) {
            Role::FORMER_STUDENT => $this->generator->dateTimeBetween('-30 years', '-19 years'),
            Role::GUARDIAN => $this->generator->dateTimeBetween('-60 years', '-25 years'),
            Role::GRADE_TEACHER, Role::ASSISTANT_TEACHER, Role::CURRICULAR_TEACHER, Role::SPECIAL_TEACHER, Role::PROFESSOR => $this->generator->dateTimeBetween('-65 years', '-25 years'),
            Role::CLASS_ASSISTANT => $this->generator->dateTimeBetween('-50 years', '-20 years'),
            Role::LIBRARIAN => $this->generator->dateTimeBetween('-65 years', '-30 years'),
            Role::COOPERATIVE => $this->generator->dateTimeBetween('-70 years', '-30 years'),
            default => $this->generator->dateTimeBetween('-70 years', '-30 years'), // For director, regent, secretary
        };
    }

    /**
     * Generate a fake birthdate based on DNI number
     */
    public function birthdateFromDNI(string $dni): \DateTime
    {
        $dniNumber = (int) $dni;

        // Determine birth year range based on DNI number
        $birthYear = $this->getBirthYearFromDNI($dniNumber);

        // Generate a random date within that year
        $startDate = new \DateTime("{$birthYear}-01-01");
        $endDate = new \DateTime("{$birthYear}-12-31");

        return $this->generator->dateTimeBetween($startDate, $endDate);
    }

    /**
     * Get birth year range based on DNI number (reverse of getDNIRangeForBirthYear)
     */
    private function getBirthYearFromDNI(int $dni): int
    {
        foreach (self::$dniYearRanges as $range) {
            if ($dni >= $range['dni_min'] && $dni <= $range['dni_max']) {
                return $this->generator->numberBetween($range['year_min'], $range['year_max']);
            }
        }

        // Fallback for DNI numbers outside the defined ranges
        return $this->generator->numberBetween(2028, 2030);
    }

    /**
     * Convert weighted array to flat array for random selection
     */
    private function getWeightedArray(array $weightedArray): array
    {
        $flatArray = [];
        foreach ($weightedArray as $item => $weight) {
            for ($i = 0; $i < $weight; $i++) {
                $flatArray[] = $item;
            }
        }
        return $flatArray;
    }

    /**
     * Get DNI range based on birth year for realistic distribution
     */
    private function getDNIRangeForBirthYear(int $birthYear): array
    {
        foreach (self::$dniYearRanges as $range) {
            if ($birthYear >= $range['year_min'] && $birthYear <= $range['year_max']) {
                return [$range['dni_min'], $range['dni_max']];
            }
        }

        // Fallback for years outside the defined ranges
        return [70000000, 75000000];
    }

    public function sanLuisEmail($firstName, $lastName): string
    {
        return 'FAKE_' . Str::slug($firstName . '.' . $lastName) . '@sanluis.edu.ar';
    }

    
    public function calculateAge(\DateTime $birthdate): int
    {
        return $birthdate->diff(new \DateTime())->y;
    }
}
