<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WhatsappLoginController extends Controller
{
    /**
     * Handle incoming webhook from WhatsApp Gateway.
     */
    public function handleWebhook(Request $request)
    {
        // Debugging: Log incoming webhook to wa.txt
        @file_put_contents(storage_path('logs/wa.txt'), "[" . date('Y-m-d H:i:s') . "] " . json_encode($request->all()) . PHP_EOL, FILE_APPEND);

        // For testing/debugging, you might want to log incoming webhook payloads:
        // Log::info('WA Webhook: ', $request->all());

        $event = $request->input('event');
        $deviceId = $request->input('device_id');
        
        if ($event !== 'message') {
            return response()->json(['status' => 'ignored_not_message']);
        }

        $payload = $request->input('payload');
        if (!$payload || !isset($payload['body']) || !isset($payload['from'])) {
            return response()->json(['status' => 'ignored_invalid_payload']);
        }

        // Prevent loops by ignoring messages sent from the bot itself
        if (isset($payload['is_from_me']) && $payload['is_from_me'] === true) {
            return response()->json(['status' => 'ignored_from_me']);
        }

        $body = trim($payload['body']);
        $fromLid = $payload['from']; // e.g. "628123456789@s.whatsapp.net"
        
        // Extract number from JID
        $phoneNumber = explode('@', $fromLid)[0];

        // Check if message is exactly "login" (case-insensitive)
        if (strtolower($body) !== 'login') {
            return response()->json(['status' => 'ignored_not_login']);
        }

        // Find user by whatsapp number
        // Normalize checking in case db has leading zero but incoming is 628
        $user = User::where('whatsapp', $phoneNumber)
                    ->orWhere('whatsapp', '0' . substr($phoneNumber, 2))
                    ->orWhere('whatsapp', '+' . $phoneNumber)
                    ->first();

        if (!$user) {
            \App\Services\WaGatewayService::sendMessage($phoneNumber, "Nomor WhatsApp Anda tidak terdaftar di sistem kami.");
            return response()->json(['status' => 'user_not_found']);
        }

        if (!$user->is_active) {
            \App\Services\WaGatewayService::sendMessage($phoneNumber, "Akun Anda sedang menunggu persetujuan Admin.");
            return response()->json(['status' => 'user_inactive']);
        }

        // Generate temporary signed URL valid for 5 minutes
        $loginUrl = URL::temporarySignedRoute(
            'whatsapp.login', now()->addMinutes(5), ['user' => $user->id]
        );

        $replyText = "Halo {$user->name}!\n\nBerikut adalah tautan login Anda:\n$loginUrl\n\nTautan ini hanya berlaku selama 5 menit dan hanya bisa digunakan satu kali.";
        
        \App\Services\WaGatewayService::sendMessage($phoneNumber, $replyText);

        return response()->json(['status' => 'success']);
    }



    /**
     * Handle the auto login from the signed URL.
     */
    public function autoLogin(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('login')->with('status', 'Tautan login telah kedaluwarsa atau tidak valid.');
        }

        if (!$user->is_active) {
            return redirect()->route('login')->with('status', 'Akun Anda tidak aktif.');
        }

        Auth::login($user);

        return redirect()->intended('/console/dashboard');
    }
}
