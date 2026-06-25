<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Setting;
use App\Models\TransactionProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        
        // Extract number from JID for login logic
        $phoneNumber = explode('@', $fromLid)[0];

        // Check if this message is from the Transaction Proof Group
        $waProofGroupId = Setting::get('wa_proof_group_id');
        $chatId = $payload['chat_id'] ?? '';
        
        if ($waProofGroupId && ($fromLid === $waProofGroupId || $chatId === $waProofGroupId)) {
            // Check if it's an image upload
            if (isset($payload['image'])) {
                $imagePathUrl = '';
                $caption = '';
                
                if (is_array($payload['image'])) {
                    $imagePathUrl = $payload['image']['path'] ?? '';
                    $caption = $payload['image']['caption'] ?? '';
                } elseif (is_string($payload['image'])) {
                    $imagePathUrl = $payload['image'];
                    $caption = $payload['body'] ?? ''; // Some WAG puts caption in body if image is string
                }
                
                if (!empty($imagePathUrl)) {
                    // Determine file name from caption or timestamp
                    $proofName = !empty($caption) ? trim($caption) : now()->format('Y-m-d H:i:s');
                    
                    // Download image
                    $imageUrl = "https://wag.anam.ch/" . ltrim($imagePathUrl, '/');
                    try {
                    $imageContent = file_get_contents($imageUrl);
                    if ($imageContent) {
                        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (!$extension) $extension = 'jpg';
                        
                        $filename = 'transaction_proofs/' . Str::uuid() . '.' . $extension;
                        Storage::disk('public')->put($filename, $imageContent);
                        
                        // Find user or default to first admin user
                        $user = User::where('whatsapp', $phoneNumber)
                            ->orWhere('whatsapp', '0' . substr($phoneNumber, 2))
                            ->orWhere('whatsapp', '+' . $phoneNumber)
                            ->first();
                            
                        $userId = $user ? $user->id : User::first()->id;
                        
                        TransactionProof::create([
                            'user_id' => $userId,
                            'name' => $proofName,
                            'file_path' => $filename,
                        ]);
                        
                        \App\Services\WaGatewayService::sendMessage($chatId ?: $fromLid, "Bukti Transaksi '$proofName' berhasil disimpan.");
                        return response()->json(['status' => 'proof_saved']);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to download WA image: " . $e->getMessage());
                }
                }
            }
            
            // If it's from the group but not an image, we just ignore it
            return response()->json(['status' => 'ignored_not_image']);
        }

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
