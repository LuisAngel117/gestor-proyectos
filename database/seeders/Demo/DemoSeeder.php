<?php

namespace Database\Seeders\Demo;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoUsersSeeder::class,
            \Database\Seeders\ProfilesSeeder::class,
            DemoTeamsSeeder::class,
            DemoProjectsSeeder::class,
            DemoSprintsSeeder::class,
            DemoBacklogSeeder::class,
            DemoTasksSeeder::class,
            DemoAssignmentsSeeder::class,
            DemoDependenciesSeeder::class,
            DemoChecklistSeeder::class,
            DemoTimeEntriesSeeder::class,
            DemoCommentsSeeder::class,
            DemoAttachmentsSeeder::class,
            DemoNotificationsSeeder::class,
        ]);
    }
}
