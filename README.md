# Email Gateway API

Laravel 12 API with OAuth, Laravel Passport and Queue jobs

## Setup

### Prerequisites
* PHP 8.2+
* Composer
* MySQL 

### Installation
1. Clone the repository.
2. Install dependencies: composer install
3. Copy environment file: cp .env.example .env
4. Configure your database in .env.
5. Set the Mailer to Log and Queue to Database in .env
    ```
    MAIL_MAILER=log
    QUEUE_CONNECTION=database
6. Generate keys and migrate:
    ```
    php artisan key:generate
    php artisan migrate
    php artisan passport:install
7. Start the server and queue worker (in separate terminals):
    ```
    # Terminal 1: Serve the API
    php artisan serve

    # Terminal 2: Process the Queue
    php artisan queue:work

## Sending a Test Request

1. Create a User & Token (via Tinker)
    ```
    php artisan tinker
    $user = App\Models\User::factory()->create();
    $token = $user->createToken('TestToken')->accessToken;

2. Send POST Request via Postman
    * URL: http://127.0.0.1:8000/api/v1/emails
    * Method: POST
    * Headers:
        * Authorization: Bearer YOUR_ACCESS_TOKEN
        * Content-Type: application/json
        * Accept: application/json
    * Body:
    ```
    {
        "to": "recipient@example.com",
        "from": "sender@example.com",
        "subject": "Hello World",
        "body": "This is a test email content."
    }

## Design Assumptions & Notes
* Email are not being sent and using the log driver.
* API returns 202 response immediately after validation to prevent the client from waiting for the mail server.
* I used database queue driver for simplicity, in a high-volume production environment preferred redis.
