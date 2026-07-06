<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZapUpWhatsAppService
{
    /**
     * Send WhatsApp message via ZapUp
     *
     * @param string $mobile  (with country code, e.g. 9198XXXXXXXX)
     * @param string $message
     * @param array  $extra   (optional extra payload)
     *
     * @return bool
     */
    public static function send(string $mobile, string $message, array $extra = []): bool
    {
        try {

            // Normalize mobile number
            $mobile = self::formatMobile($mobile);

            if (!$mobile) {
                Log::warning('ZapUp: Invalid mobile number');
                return false;
            }

            $payload = array_merge([
                'sender'  => config('services.zapup.sender'),
                'number'  => $mobile,
                'message' => $message,
            ], $extra);

            $response = Http::timeout(20)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.zapup.key'),
                    'Accept'        => 'application/json',
                ])
                ->post(config('services.zapup.url'), $payload);

            // Log response
            Log::info('ZapUp WhatsApp Response', [
                'mobile'   => $mobile,
                'payload'  => $payload,
                'response' => $response->json(),
                'status'   => $response->status(),
            ]);

            return $response->successful();

        } catch (\Throwable $e) {

            Log::error('ZapUp WhatsApp Exception', [
                'mobile'  => $mobile ?? null,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Format mobile number to international format
     *
     * @param string $mobile
     * @return string|null
     */
    private static function formatMobile(string $mobile): ?string
    {
        $mobile = preg_replace('/\D/', '', $mobile);

        // India numbers
        if (strlen($mobile) === 10) {
            return '91' . $mobile;
        }

        if (strlen($mobile) === 12 && str_starts_with($mobile, '91')) {
            return $mobile;
        }

        return null;
    }
}
