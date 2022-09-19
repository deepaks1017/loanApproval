<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userExists = DB::table('users')->where('username','admin')->first();
        if(empty($userExists)){
            
            DB::table('users')->insert([
                'username' => 'admin',
                'secret_key' => 'cuaS9D0HRe',
                'password' => Hash::make('password'),
                'user_role' => 'admin'
            ]);
        } else {
            dump('Admin user already exists');
        }

        $userExists = DB::table('users')->where('username','demouser')->first();
        if(empty($userExists)){
            DB::table('users')->insert([
                'username' => 'demouser',
                'secret_key' => 'Z82fG73OQ4',
                'password' => Hash::make('password'),
                'user_role' => 'customer'
            ]);
        } else {
            dump('Demo user already exists');
        }
        
        
    }
}
