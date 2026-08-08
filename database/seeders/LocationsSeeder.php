<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    /**
     * Seed the application's locations.
     */
    public function run(): void
    {
        $italy = Location::create([
            'name' => 'Italija',
        ]);

        $italy->translations()->createMany([
            [
                'lang' => 'en',
                'name' => 'Italy',
                'slug' => 'italy',
                'meta_description' => 'Italy',
                'meta_keywords' => 'Italy',
            ],
            [
                'lang' => 'lt',
                'name' => 'Italija',
                'slug' => 'italija',
                'meta_description' => 'Italija',
                'meta_keywords' => 'Italija',
            ],
        ]);

        $poland = Location::create([
            'name' => 'Lenkija',
        ]);

        $poland->translations()->createMany([
            [
                'lang' => 'en',
                'name' => 'Poland',
                'slug' => 'poland',
                'meta_description' => 'Poland',
                'meta_keywords' => 'Poland',
            ],
            [
                'lang' => 'lt',
                'name' => 'Lenkija',
                'slug' => 'lenkija',
                'meta_description' => 'Lenkija',
                'meta_keywords' => 'Lenkija',
            ],
        ]);
    }
}
