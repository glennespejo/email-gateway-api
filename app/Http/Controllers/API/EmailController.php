<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailRequest;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use Illuminate\Http\JsonResponse;

class EmailController extends Controller
{
    public function send(EmailRequest $request): JsonResponse
    {

        //create pending email
        $email = Email::create([
            'to' => $request->to,
            'from' => $request->from,
            'subject' => $request->subject,
            'body' => $request->body,
            'status' => 'pending'
        ]);

        //send email to queue job
        SendEmailJob::dispatch($email);

        return response()->json([
            'message' => 'Email queued successfully.',
            'data' => $email
        ], 202);
    }
}
