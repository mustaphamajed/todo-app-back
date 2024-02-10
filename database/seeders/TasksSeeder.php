<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Task::create([
            'title' => 'title',
            'description' => 'description',
            'status' => 'pending',
            'user_id' => 1
        ]);
        //

    }
}
