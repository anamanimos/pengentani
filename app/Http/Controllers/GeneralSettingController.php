<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    /**
     * Display the General Settings page.
     */
    public function index()
    {
        $appName = Setting::get('app_name', config('app.name', 'Pengen Tani'));
        $appTagline = Setting::get('app_tagline', 'Pengelolaan Pertanian & Investasi Kebun');
        $companyName = Setting::get('company_name', 'PT Pengen Tani Indonesia');
        $contactEmail = Setting::get('contact_email', 'admin@pengentani.my.id');
        $contactPhone = Setting::get('contact_phone', '6281234567890');
        $address = Setting::get('address', 'Indonesia');
        $timezone = Setting::get('timezone', config('app.timezone', 'Asia/Jakarta'));
        $currencySymbol = Setting::get('currency_symbol', 'Rp');
        $geminiApiKey = Setting::get('gemini_api_key', config('services.gemini.api_key', env('GEMINI_API_KEY', '')));
        $geminiModel = Setting::get('gemini_model', 'gemini-3.6-flash');

        return view('settings.general', compact(
            'appName',
            'appTagline',
            'companyName',
            'contactEmail',
            'contactPhone',
            'address',
            'timezone',
            'currencySymbol',
            'geminiApiKey',
            'geminiModel'
        ));
    }

    /**
     * Update General Settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_tagline' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'timezone' => 'required|string|max:100',
            'currency_symbol' => 'required|string|max:10',
            'gemini_api_key' => 'nullable|string|max:255',
            'gemini_model' => 'nullable|string|max:100',
        ]);

        Setting::set('app_name', trim($request->app_name));
        Setting::set('app_tagline', trim($request->app_tagline ?? ''));
        Setting::set('company_name', trim($request->company_name ?? ''));
        Setting::set('contact_email', trim($request->contact_email ?? ''));
        Setting::set('contact_phone', trim($request->contact_phone ?? ''));
        Setting::set('address', trim($request->address ?? ''));
        Setting::set('timezone', trim($request->timezone));
        Setting::set('currency_symbol', trim($request->currency_symbol));

        if ($request->has('gemini_api_key')) {
            Setting::set('gemini_api_key', trim($request->gemini_api_key ?? ''));
        }
        if ($request->has('gemini_model')) {
            Setting::set('gemini_model', trim($request->gemini_model ?? 'gemini-3.6-flash'));
        }

        return redirect()->back()->with('success', 'Pengaturan Umum & Integrasi Gemini berhasil diperbarui.');
    }

    /**
     * Test Google Gemini API connection via AJAX.
     */
    public function testGemini(Request $request)
    {
        $apiKey = $request->input('api_key');
        $model = $request->input('model');

        $result = \App\Services\GeminiVisionService::testConnection($apiKey, $model);

        return response()->json($result);
    }

    /**
     * Fetch available models from Gemini API via AJAX.
     */
    public function getGeminiModels(Request $request)
    {
        $apiKey = $request->input('api_key');
        $result = \App\Services\GeminiVisionService::getAvailableModels($apiKey);

        return response()->json($result);
    }
}
