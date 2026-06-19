<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappSettingController extends Controller
{
    /**
     * Show the WhatsApp Connection Settings page.
     */
    public function index()
    {
        $status = 'unknown';
        $qrData = null;
        $jid = null;

        try {
            $username = env('WA_GATEWAY_USERNAME', 'admin');
            $password = env('WA_GATEWAY_PASSWORD', 'admin');

            // Check status
            $response = Http::withBasicAuth($username, $password)
                ->withHeaders([
                    'X-Device-Id' => 'tanisync',
                ])->get('https://wag.anam.ch/app/status');

            if ($response->successful()) {
                $data = $response->json();
                
                // If the app is connected and logged in
                if (isset($data['results']) && $data['results']['is_connected'] && $data['results']['is_logged_in']) {
                    $status = 'connected';
                    $jid = $data['results']['jid'] ?? null;
                } else {
                    $status = 'disconnected';
                }
            } else {
                $status = 'disconnected';
            }

            // If disconnected, we try to generate a QR code for login
            if ($status === 'disconnected') {
                $loginResponse = Http::withBasicAuth($username, $password)
                    ->withHeaders([
                        'X-Device-Id' => 'tanisync',
                    ])->get('https://wag.anam.ch/app/login');

                if ($loginResponse->successful()) {
                    $loginData = $loginResponse->json();
                    if (isset($loginData['results'])) {
                        // Pass the whole results object (could be qr_link or qr_code)
                        $qrData = $loginData['results'];
                    }
                }
            }

        } catch (\Exception $e) {
            Log::error("Failed to connect to WA Gateway: " . $e->getMessage());
            $status = 'error';
        }

        return view('whatsapp.index', compact('status', 'qrData', 'jid'));
    }
}
