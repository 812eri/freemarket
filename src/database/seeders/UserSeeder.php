<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Address;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. 商品を出品するユーザー（出品者）
        $seller = User::create([
            'id' => 1,
            'name' => '出品 太郎',
            'email' => 'seller@test.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'profile_completed' => true,
        ]);

        Address::create([
            'user_id' => $seller->id,
            'post_code' => '123-4567',
            'street_address' => '東京都渋谷区道玄坂1-2-3',
            'building_name' => 'テックビル101',
        ]);

        // 2. ログインして画面を確認するユーザー（閲覧者）
        $buyer = User::create([
            'id' => 2,
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'profile_completed' => true,
        ]);

        Address::create([
            'user_id' => $buyer->id,
            'post_code' => '987-6543',
            'street_address' => '大阪府大阪市北区梅田1-1',
            'building_name' => 'グランフロント大阪',
        ]);
    }
}
