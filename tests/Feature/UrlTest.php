<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Faker\Factory as Faker;
use App\User;
use App\Url;
use JWTAuth;

class UrlTest extends TestCase
{
    /**
     * Create fake user.
     *
     * @return User
     */
    protected function createFakeUser()
    {
        $faker = Faker::create();
        $username = $faker->name;
        $email = $faker->unique()->safeEmail;
        $password = str_random(10);

        $this->call('POST', '/api/v1/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password
        ]);

        return User::where('username', '=', $username)->get()->first();
    }

    /**
     * create url unauthenticated user test.
     *
     * @return void
     */
    public function testCreateUrlUnauthenticatedUser()
    {
        $faker = Faker::create();
        // sending the request and test status and json response
        $response = $this->json('POST', '/api/v1/urls/', [
                'url' => $faker->url
            ])
            ->assertStatus(400)
            ->assertJson([
                'error' => 'token_not_provided'
            ]);
    }

    /**
     * create url token expired test.
     *
     * @return void
     */
    public function testCreateUrlTokenExpired()
    {
        $faker = Faker::create();

        // sending the request and test status and json response
        $response = $this->withHeaders([
                'Authorization' => 'bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hcGkvdjEvcmVnaXN0ZXIiLCJpYXQiOjE1MjQzNTI2NzEsImV4cCI6MTUyNDM1NjI3MSwibmJmIjoxNTI0MzUyNjcxLCJqdGkiOiJkT0FmdkFPNVloSGFKcldoIn0.q_oC9zg2iaZ57n_OTUNE5IDy3yysyoTMLLIXgov-DCo',
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => $faker->url
            ])
            ->assertStatus(401)
            ->assertJson([
                'error' => 'token_expired'
            ]);
    }

    /**
     * create url authenticated user test.
     *
     * @return void
     */
    public function testCreateUrlAuthenticatedUser()
    {
        $faker = Faker::create();
        $url = $faker->url;

        $user = $this->createFakeUser();
        $token = JWTAuth::fromUser($user);

        // sending the request and test status and json response
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => $url
            ])
            ->assertStatus(200)
            ->assertJson([
                'url' => [
                    'id' => Url::latest()->first()->id,
                    'url' => $url
                ]
            ])
            ->assertJsonStructure([
                'url' => [
                    'id',
                    'url'
                ]
            ]);
    }

    /**
     * create url with missing url test.
     *
     * @return void
     */
    public function testCreateUrlWithUrlMissing()
    {
        $user = User::latest()->first();
        $token = JWTAuth::fromUser($user);

        // sending the request and test status and json response
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('POST', '/api/v1/urls/', [
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * create url with bad url test.
     *
     * @return void
     */
    public function testCreateUrlWithBadUrl()
    {
        $user = User::latest()->first();
        $token = JWTAuth::fromUser($user);

        // sending the request and test status and json response
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => 'bad-url'
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * create url that exist for same user test.
     *
     * @return void
     */
    public function testCreateUrlThatExistForSameUser()
    {
        $faker = Faker::create();
        $url = $faker->url;

        $user = User::latest()->first();
        $token = JWTAuth::fromUser($user);

        // sending the request and test status and json response
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => $url
            ])
            ->assertStatus(200)
            ->assertJson([
                'url' => [
                    'id' => Url::latest()->first()->id,
                    'url' => $url
                ]
            ])
            ->assertJsonStructure([
                'url' => [
                    'id',
                    'url'
                ]
            ]);

        // creating the url again for the same user
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => $url
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }
}
