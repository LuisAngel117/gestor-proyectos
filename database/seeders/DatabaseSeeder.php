<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\Demo\DemoSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoSeed = env('DEMO_SEED');
        if ($demoSeed === null) {
            $demoSeed = app()->environment('local');
        } else {
            $demoSeed = filter_var($demoSeed, FILTER_VALIDATE_BOOL);
        }

        if ($demoSeed) {
            $this->call(DemoSeeder::class);
            $this->command->info('Demo: seeding completo.');
            return;
        }

        $this->call([
            UsersSeeder::class,
            ProfilesSeeder::class,
            TeamsSeeder::class,
        ]);

        $this->command->info('🎉 Base de datos poblada exitosamente');
    }
}
