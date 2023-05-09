<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function sendOTP($to, $code)
    {
        $this->client->messages->create(
            "+91".$to,
            [
                'from' => env('TWILIO_FROM_NUMBER'),
                'body' => "Your OTP is: {$code}",
                'message' => "Verification Code"
            ]
        );
        return $code;
    }
}
