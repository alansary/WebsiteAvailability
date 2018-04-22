<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hash;
use Faker\Factory as Faker;
use App\User;
use JWTAuth;

class UserTest extends TestCase
{
    /**
     * Registering a user test.
     *
     * @return void
     */
    public function testRegisterUser()
    {
        $faker = Faker::create();
        $username = $faker->name;
        $email = $faker->unique()->safeEmail;
        $password = str_random(10);
        $hashed_password = Hash::make($password);

        $response = $this->json('POST', '/api/v1/register', [
                'username' => $username,
                'email' => $email,
                'password' => $hashed_password,
                'password_confirmation' => $hashed_password
            ])
            ->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => User::latest()->first()->id,
                    'username' => $username,
                    'email' => $email
                ]
            ])
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'username',
                    'email'
                ],
                'token'
            ]);
        return $hashed_password;
    }

    /**
     * @depends testRegisterUser
     */
    public function testLoginUser($password)
    {
        $user = User::latest()->first();

        $response = $this->json('POST', '/api/v1/login', [
                'username' => $user->username,
                'password' => $password
            ])
            ->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ]
            ])
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'username',
                    'email'
                ],
                'token'
            ]);
    }
}
