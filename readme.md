# Website Availability App API
## Description
API for a Website Availability app which will help developers to monitor their online websites/apps and get notified whatever one of their website/app is down.

## Installation


#### Dependencies:
* [Laravel 5.6](https://github.com/laravel/laravel)
* [JWTAuth](https://github.com/tymondesigns/jwt-auth)
* [Laravel Fractal](https://github.com/spatie/laravel-fractal)

**1-** Clone the repository.

```bash
$ git clone https://github.com/alansary/WebsiteAvailability.git
```

**2-** Run Composer to install or update the requirements.

```bash
$ composer install
```

or

```bash
$ composer update
```

**3-** Create the database and change the credentials and the name of the database in .env file.

```bash
$ cp .env.example .env
```

**4-** Run the following command to generate the secret key and place the secret key in config/app.

```bash
$ php artisan key:generate
```
```php
'key' => env('APP_KEY','You-Generated-Key'),
```

**5-** Generate JWT Secret and place it in .env like JWT_SECRET=Your-Secret-Key and replace it in config/jwt.
First open vendor/tymon/jwt-auth/src/Commands/JWTGenerateCommand.php and add the following function
```php
    public function handle() { 
        $this->fire();
    }
```
Then generate the key
```bash
$ php artisan jwt:generate
```
```php
'secret' => env('JWT_SECRET', 'Your-Secret-Key'),
```

**6-** Migrate the database
```bash
$ php artisan migrate
```

**7-** Run the project
```bash
$ php artisan serve
```

----
## APIs:
----

api/v1/register -- POST
#### Request:
```json
{
	"username": "alansary",
	"email": "mohamed_alansary@rocketmail.com",
	"password": "123456",
	"password_confirmation": "123456"
}
```
#### Response:
```json
{
    "user": {
        "id": 1,
        "username": "alansary",
        "email": "mohamed_alansary@rocketmail.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjcsImlzcyI6Imh0dHA6Ly93ZWJzaXRlYXZhaWxhYmlsaXR5LmxhcmF2ZWwuY29tL2FwaS92MS9yZWdpc3RlciIsImlhdCI6MTUyNDI1ODA4MCwiZXhwIjoxNTI0MjYxNjgwLCJuYmYiOjE1MjQyNTgwODAsImp0aSI6InpQaUtqSnhyaXdXeWsyWm4ifQ.wRYKcz-e7x7qMeEmiU-3AMKP9cx27MWRfHsASSymqbY"
}
```

api/v1/login -- POST
#### Request:
```json
{
	"username": "alansary",
	"password": "123456"
}
```
#### Response:
```json
{
    "user": {
        "id": 1,
        "username": "alansary",
        "email": "mohamed_alansary@rocketmail.com"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjcsImlzcyI6Imh0dHA6Ly93ZWJzaXRlYXZhaWxhYmlsaXR5LmxhcmF2ZWwuY29tL2FwaS92MS9yZWdpc3RlciIsImlhdCI6MTUyNDI1ODA4MCwiZXhwIjoxNTI0MjYxNjgwLCJuYmYiOjE1MjQyNTgwODAsImp0aSI6InpQaUtqSnhyaXdXeWsyWm4ifQ.wRYKcz-e7x7qMeEmiU-3AMKP9cx27MWRfHsASSymqbY"
}
```

api/v1/urls -- POST
#### Request:
```json
{
	"url": "http://www.github.com"
}
```
#### Response:
```json
{
    "url": {
        "id": 1,
        "url": "http://www.github.com",
        "isActive": true
    }
}
```

api/v1/urls -- GET
#### Request:
api/v1/urls?id=1
#### Response:
```json
{
    "url": {
        "id": 1,
        "url": "http://www.github.com",
        "isActive": true
    }
}
```

api/v1/urls -- DELETE
#### Request:
```json
api/v1/urls?id=1
```
#### Response:
```json
{
    "message": "URL deleted successfully"
}
```

api/v1/urls -- PATCH
#### Request:
```json
{
	"id": 1,
	"url": "http://www.urldoesntexist.com"
}
```
#### Response:
```json
{
    "url": {
        "id": 1,
        "url": "http://www.urldoesntexist.com",
        "isActive": false
    }
}
```

api/v1/urls/all -- GET
#### Request:
```json
{
	"url": "http://www.github.com"
}
```
#### Response:
```json
{
    "urls": [
        {
            "id": 1,
            "url": "http://www.github.com",
            "isActive": true
        },
        {
            "id": 2,
            "url": "http://www.urldoesntexist.com",
            "isActive": false
        }
    ]
}
```

----
## Note:
----

#### In case of error, the response is returned as follows:
```json
{
    "errors": [
		"error #1",
		"error #2",
		"error #3",
		"........"
    ]
}
```

----
## Artisan Command:
----

#### To see more information about the command:
```bash
$ php artisan help CheckStatus
```
This command send messages to the users in two cases only, if the user has websites that are down or websites that became up and running.

#### Optional Arguments:
```bash
$ php artisan help CheckStatus --userId=1
```
This argument send messages to only this user id in case on of the above cases is applied.

```bash
$ php artisan help CheckStatus --urlId=1
```
This argument send messages to only the owner of this url in case on of the above cases is applied.

----
## Further Enhancements:
----

#### Queue messages and running the queue on the server using supervisord.
#### Using events and listeners to listen for the change of any url status and scheduling the command using supervisord on the server.
#### /api/v1/urls/all && /api/v1/urls/get APIs update the status of the url(s) automatically without sending an email (events and listeners)

----
## Further Notes:
----

#### Postman collection is attached with the project, don't forgot to change the base url or create a virtual environment with the same name on your local host.
#### You can use mailtrap to test sending mails
#### In order to test the down mails, you can disconnect from the network and send API call to get a url or user urls and it will automatically update the url(s) status to be false, then connect to the network and try from your command line
```bash
$ php artisan CheckStatus
```
#### And it will send the email with the down time 

----
## Sample Email:
----

Hi alansary,

Your report for the urls/apps availability is as follows:

The url/app "http://www.test.com" is now up, it was down for 0 week(s), 0 day(s), 0 hour(s), 0 minute(s) and 10 second.

The url/app "http://www.test2.com" is still down, it is down since 0 week(s), 0 day(s), 1 hour(s), 32 minute(s) and 44 second.

The url/app "http://www.facebook.com" is now up, it was down for 0 week(s), 0 day(s), 0 hour(s), 0 minute(s) and 19 second.

Thanks,
The Website Availability App Team

© 2018 WebsiteAvailabilityApp. All rights reserved.

WebsiteAvailabilityApp, LLC