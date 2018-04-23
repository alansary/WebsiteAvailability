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
        $url = Url::latest()->first();

        $user = User::latest()->first();
        $token = JWTAuth::fromUser($user);

        // creating the url again for the same user
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => $url->url
            ])
            ->assertStatus(400)
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * get url with unauthenticated user test.
     *
     * @return void
     */
    public function testGetUrlWithUnauthenticatedUser()
    {
        $url = Url::latest()->first();
        $token = JWTAuth::fromUser(User::latest()->first());

        // get the url
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('GET', '/api/v1/urls?id='.$url->id)
            ->assertStatus(200)
            ->assertJson([
                'url' => [
                    'id' => $url->id,
                    'url' => $url->url
                ]
            ])
            ->assertJsonStructure([
                'url' => [
                    'id',
                    'url',
                    'isActive'
                ]
            ]);
    }

    /**
     * get url with authenticated user test.
     *
     * @return void
     */
    public function testGetUrlWithAuthenticatedUser()
    {
        // get the url
        $response = $this->json('GET', '/api/v1/urls?id='.Url::latest()->first()->id, [
            ])
            ->assertStatus(400)
            ->assertJson([
                'error' => 'token_not_provided'
            ]);
    }

    /**
     * get url that is not found test.
     *
     * @return void
     */
    public function testGetUrlThatIsNotFound()
    {
        $url = Url::latest()->first();
        $token = JWTAuth::fromUser(User::latest()->first());

        // get the url
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('GET', '/api/v1/urls?id=100000000000')
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'URL not found'
                ]
            ])
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * get url with id missing test.
     *
     * @return void
     */
    public function testGetUrlWithIdMissing()
    {
        $url = Url::latest()->first();
        $token = JWTAuth::fromUser(User::latest()->first());

        // get the url
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('GET', '/api/v1/urls')
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'The id field is required.'
                ]
            ])
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * get url of other user test.
     *
     * @return integer
     */
    public function testGetUrlOfOtherUser()
    {
        $first_user = User::all()->first();
        $first_token = JWTAuth::fromUser($first_user);

        // creating a url for the first user
        $faker = Faker::create();
        $url = $faker->url;
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $first_token,
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => $faker->url
            ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'url' => [
                    'id',
                    'url'
                ]
            ]);
        $url_array = json_decode($response->getContent(), true);

        return $url_array['url']['id'];
    }

    /**
     * get url of other user continued test.
     *
     * @return void
     * @depends testGetUrlOfOtherUser
     */
    public function testGetUrlOfOtherUserCont($url_id)
    {
        // getting the url using the latest user
        $latest_user = User::latest()->first();
        $latest_token = JWTAuth::fromUser($latest_user);

        // get the url
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $latest_token,
            ])
            ->json('GET', '/api/v1/urls?id='.$url_id)
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'Unauthorized'
                ]
            ])
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * get all urls of unauthenticated user test.
     *
     * @return void
     */
    public function testGetAllUrlsWithUnauthenticatedUser()
    {
        // get the urls
        $response = $this->json('GET', '/api/v1/urls/all')
            ->assertStatus(400)
            ->assertJson([
                'error' => 'token_not_provided'
            ])
            ->assertJsonStructure([
                'error'
            ]);
    }

    /**
     * create a url for latest user test.
     *
     * @return integer
     */
    public function testCreateUrlForLatestUser()
    {
        $user = User::latest()->first();
        $token = JWTAuth::fromUser($user);

        // creating a url for the first user
        $faker = Faker::create();
        $url = $faker->url;
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. $token,
            ])
            ->json('POST', '/api/v1/urls/', [
                'url' => $faker->url
            ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'url' => [
                    'id',
                    'url'
                ]
            ]);
    }

    /**
     * get all urls of authenticated user test.
     *
     * @return void
     * @depends testCreateUrlForLatestUser
     */
    public function testGetAllUrlsWithAuthenticatedUser()
    {
        // get the url
        $response = $this->withHeaders([
                'Authorization' => 'bearer'. JWTAuth::fromUser(User::latest()->first()),
            ])
            ->json('GET', '/api/v1/urls/all', [
            ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'urls' => [
                    [
                        'id',
                        'url',
                        'isActive'
                    ]
                ]
            ]);
    }
}
