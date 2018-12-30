<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App\DataLayer\User::create(
            array(
                'name' => 'Joe Schmoe',
                'email' => 'fakeuser@symphonic.com',
                'password' => Hash::make('letmein'),
            )
        );
    }
}
