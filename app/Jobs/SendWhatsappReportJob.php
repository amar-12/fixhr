<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SendWhatsappReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $client = new Client();

            $authToken = 'y49hG8WWm4BuKEomJziptjxy28FersAkKer68r74';

            $response = $client->post('https://box.zapup.press/api/send/template', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$authToken}"
                ],
                'body' => json_encode($this->payload)
            ]);

            Log::info('WhatsApp report sent successfully', [
                'phone' => $this->payload['phone'] ?? null,
                'response' => $response->getBody()->getContents()
            ]);

        } catch (\Exception $e) {

            Log::error('WhatsApp report sending failed', [
                'phone' => $this->payload['phone'] ?? null,
                'error' => $e->getMessage()
            ]);

        }
    }
}