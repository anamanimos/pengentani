<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaGatewayService
{
    /**
     * Send a text message using the WA Gateway API.
     *
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public static function sendMessage(string $phone, string $message): bool
    {
        try {
            // Normalize phone number (remove non-digits)
            $phone = preg_replace('/[^0-9]/', '', $phone);
            // Replace leading 0 with 62
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            }

            $username = env('WA_GATEWAY_USERNAME', 'admin');
            $password = env('WA_GATEWAY_PASSWORD', 'admin');

            $response = Http::withBasicAuth($username, $password)
                ->withHeaders([
                    'X-Device-Id' => 'tanisync',
                    'Content-Type' => 'application/json',
                ])->post('https://wag.nams.my.id/send/message', [
                    'phone' => $phone,
                    'message' => $message,
                    'isGroup' => false,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("WA Gateway returned error: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to send WA message: " . $e->getMessage());
            return false;
        }
    }
}
