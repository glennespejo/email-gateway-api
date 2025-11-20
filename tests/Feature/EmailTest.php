<?php

namespace Tests\Feature;

use App\Jobs\SendEmailJob;
use App\Models\Email;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Laravel\Passport\Passport;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users(): void
    {
        //try to access endpoint without token
        $response = $this->postJson('/api/v1/emails', []);
        $response->assertStatus(401);
    }

    public function test_validate_data(): void
    {
        Passport::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/emails', [
            'to' => 'not-an-email',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['to', 'from', 'subject', 'body']);
    }

    public function test_queues_email_and_stores_pending_status(): void
    {
        //fake queue to assert dispatching
        Queue::fake();
        Passport::actingAs(User::factory()->create());

        $payload = [
            'to' => 'recipient@example.com',
            'from' => 'sender@example.com',
            'subject' => 'Test Subject',
            'body' => 'Test Body'
        ];

        $response = $this->postJson('/api/v1/emails', $payload);

        //assert response
        $response->assertStatus(202)
                 ->assertJsonPath('data.status', 'pending');

        //assert database
        $this->assertDatabaseHas('emails', [
            'to' => 'recipient@example.com',
            'status' => 'pending'
        ]);

        //assert queu job
        Queue::assertPushed(SendEmailJob::class);
    }

    public function test_job_sends_email_and_updates_status(): void
    {
        //fake mail to assert mail
        Mail::fake();

        $email = Email::create([
            'to' => 'rec@test.com',
            'from' => 'send@test.com',
            'subject' => 'Sub',
            'body' => 'Body',
            'status' => 'pending'
        ]);

        //manually run the queue job
        $job = new SendEmailJob($email);
        $job->handle();

        //assert change status to sent
        $this->assertDatabaseHas('emails', [
            'id' => $email->id,
            'status' => 'sent'
        ]);
    }
}
