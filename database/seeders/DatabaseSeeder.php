<?php

namespace Database\Seeders;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user_id = \DB::table('users')->insertGetId([
          'email'             => 'tim@wickstrom.me',
          'email_verified_at' => Carbon::now()->sub('hour', 6),
          'password'          => \Hash::make('Larabel Boilderplate!'),
          'created_by'        => 1,
          'updated_by'        => 1,
          'created_at'        => Carbon::now()->sub('hour', 6),
          'updated_at'        => Carbon::now()->sub('hour', 6),
        ]);

        \DB::table('user_profiles')->insertGetId([
          'user_id'           => $user_id,
          'first_name'        => 'Tim',
          'last_name'         => 'Wickstrom',
          'created_by'        => 1,
          'updated_by'        => 1,
          'created_at'        => Carbon::now()->sub('hour', 6),
          'updated_at'        => Carbon::now()->sub('hour', 6),
        ]);

        $space_id = \DB::table('spaces')->insertGetId([
            'user_id'           => $user_id,
            'owner_id'          => $user_id,
            'name'              => 'Test Space',
            'bio'               => '',
            'website'           => null,
            'latitude'          => null,
            'longitude'         => null,
            'privacy'           => 'public',
            'created_by'        => 1,
            'updated_by'        => 1,
            'created_at'        => Carbon::now()->sub('hour', 6),
            'updated_at'        => Carbon::now()->sub('hour', 6),
        ]);

        \DB::table('space_members')->insertGetId([
            'space_id'          => $space_id,
            'user_id'           => $user_id,
            'created_by'        => 1,
            'updated_by'        => 1,
            'created_at'        => Carbon::now()->sub('hour', 6),
            'updated_at'        => Carbon::now()->sub('hour', 6),
        ]);

        \DB::table('oauth_clients')->insert([
            'id'                      => 1,
            'user_id'                 => null,
            'name'                    => 'Larabel Boilderplate Password Grant Client',
            'secret'                  => 'kUp3icrBVy6l7JTPyagp6zKQxf7AOWa0I71RMdXw',
            'provider'                => 'users',
            'redirect'                => 'http://localhost',
            'personal_access_client'  => 0,
            'password_client'         => 1,
            'revoked'                 => 0,
            'created_at'              => Carbon::now()->sub('hour', 6),
            'updated_at'              => Carbon::now()->sub('hour', 6)
        ]);
    }
}
