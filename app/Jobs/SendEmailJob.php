<?php

namespace App\Jobs;

use App\Models\Email;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    public $email;

    /**
     * Create a new job instance.
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            //build mail
            Mail::raw($this->email->body, function ($email) {
                $email->to($this->email->to)
                    ->from($this->email->from)
                    ->subject($this->email->subject);
            });
            //update status to sent
            $this->email->update(['status' => 'sent']);
        } catch (\Exception $e) {
            Log::error("[SendEmailJob] Failed to send email ID {$this->email->id}: " . $e->getMessage());
            //update status to failed
            $this->email->update(['status' => 'failed']);
        }
    }
}
