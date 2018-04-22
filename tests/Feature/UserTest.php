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
     * Create fake user data.
     *
     * @return array
     */
    protected function createFakeUserData()
    {
        $faker = Faker::create();
        $username = $faker->name;
        $email = $faker->unique()->safeEmail;
        $password = str_random(10);
        $hashed_password = Hash::make($password);

        return [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'hashed_password' => $hashed_password
        ];
    }

    /**
     * Registering a user test.
     *
     * @return string password
     */
    public function testRegisterUser()
    {
        // create fake data to register a user
        $user_data = $this->createFakeUserData();

        // sending the request and test status and json response
        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'email' => $user_data['email'],
                'password' => $user_data['password'],
                'password_confirmation' => $user_data['password']
            ])
            ->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => User::latest()->first()->id,
                    'username' => $user_data['username'],
                    'email' => $user_data['email']
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

        // return password to test login
        return $user_data['password'];
    }

    /**
     * Login a user test.
     * 
     * @return void
     * @depends testRegisterUser
     */
    public function testLoginUser($password)
    {
        // get the last registered user
        $user = User::latest()->first();

        // send login request and test status and json response
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

    /**
     * Login a user with missing data test.
     *
     * @return void
     * @depends testRegisterUser
     */
    public function testLoginUserWithDataMissing($password)
    {
        // get the last registered user
        $user = User::latest()->first();

        // missing username
        $response = $this->json('POST', '/api/v1/login', [
                'password' => $user->password
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);

        // missing password
        $response = $this->json('POST', '/api/v1/login', [
                'username' => $user->username
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * Login a user with invalid data test.
     *
     * @return void
     * @depends testRegisterUser
     */
    public function testLoginUserWithInvalidData($password)
    {
        // create fake data to register a user
        $user_data = $this->createFakeUserData();

        // get the last registered user
        $user = User::latest()->first();

        // unauthorized user
        $response = $this->json('POST', '/api/v1/login', [
                'username' => $user_data['username'],
                'password' => $password
            ])
            ->assertStatus(401)
            ->assertJsonStructure([
                'errors'
            ]);

        // invalid username
        $response = $this->json('POST', '/api/v1/login', [
                'username' => '',
                'password' => $password
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);

        // unauthorized user
        $response = $this->json('POST', '/api/v1/login', [
                'username' => $user->username,
                'password' => '123456'
            ])
            ->assertStatus(401)
            ->assertJsonStructure([
                'errors'
            ]);

        // invalid password
        $response = $this->json('POST', '/api/v1/login', [
                'username' => $user->username,
                'password' => ''
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * Registering a user with missing data test.
     *
     * @return void
     */
    public function testRegisterUserWithDataMissing()
    {
        // create fake data to register a user
        $user_data = $this->createFakeUserData();

        // Missing username
        $response = $this->json('POST', '/api/v1/register', [
                'email' => $user_data['email'],
                'password' => $user_data['password'],
                'password_confirmation' => $user_data['password']
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);

        // Missing email
        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'password' => $user_data['password'],
                'password_confirmation' => $user_data['password']
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);

        // Missing password
        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'email' => $user_data['email'],
                'password_confirmation' => $user_data['password']
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);

        // Missing password_confirmation
        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'email' => $user_data['email'],
                'password' => $user_data['password']
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * Registering a user with invalid data test.
     *
     * @return void
     */
    public function testRegisterUserWithInvalidData()
    {
        // create fake data to register a user
        $user_data = $this->createFakeUserData();

        // Invalid username
        $response = $this->json('POST', '/api/v1/register', [
                'username' => 1234,
                'email' => $user_data['email'],
                'password' => $user_data['password'],
                'password_confirmation' => $user_data['password']
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);

        // Invalid email
        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'email' => $user_data['username'],
                'password' => $user_data['password'],
                'password_confirmation' => $user_data['password']
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);

        // Invalid password
        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'email' => $user_data['username'],
                'password' => '1234',
                'password_confirmation' => '1234'
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * Registering a user with non matching password test.
     *
     * @return void
     */
    public function testRegisterUserWithNonMatchingPassword()
    {
        // create fake data to register a user
        $user_data = $this->createFakeUserData();

        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'email' => $user_data['email'],
                'password' => $user_data['password'],
                'password_confirmation' => '123456'
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * Registering a user with an email that exist test.
     *
     * @return void
     */
    public function testRegisterUserWithAnEmailThatExist()
    {
        // create fake data to register a user
        $user_data = $this->createFakeUserData();
        // getting the last created user
        $user = User::latest()->first();

        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user_data['username'],
                'email' => $user->email,
                'password' => $user_data['password'],
                'password_confirmation' => '123456'
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * Registering a user with a username that exist test.
     *
     * @return void
     */
    public function testRegisterUserWithAUsernameThatExist()
    {
        // create fake data to register a user
        $user_data = $this->createFakeUserData();
        // getting the last created user
        $user = User::latest()->first();

        $response = $this->json('POST', '/api/v1/register', [
                'username' => $user->username,
                'email' => $user_data['email'],
                'password' => $user_data['password'],
                'password_confirmation' => '123456'
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }
}
