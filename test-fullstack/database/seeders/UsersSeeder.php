<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batch_user = [
            [
                "id" => 1,
                "name" => "testing1",
                "username" => "user1",
                "password" => "user1",
            ],
            [
                "id" => 2,
                "name" => "testing2",
                "username" => "user2",
                "password" => "user2",
            ]
        ];

        foreach ($batch_user as $key => $value) {
            User::updateOrCreate(
                ['id' => $value['id']],
                $value
            );
        }
    }
}
