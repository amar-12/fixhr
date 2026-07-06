<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SendWhatsappCampaign extends Command
{
    protected $signature = 'send:campaign';
    protected $description = 'Send WhatsApp campaign';

    public function handle()
    {
        $numbers = [
            '+918048404493','+918878888120','+918085535992','+917880128802','+917773800067','+919302921773','+917471127790','+917713508633','+918856811919','+917471128577','+917225016966','+917714287200','+918808171511','+918319876071','+917566831553','+918109279211', '+919340526595'
        ];

        $token = 'y49hG8WWm4BuKEomJziptjxy28FersAkKer68r74';

        $client = new Client([
            'timeout' => 30
        ]);

        foreach ($numbers as $phone) {

            $this->info("Sending to ".$phone);

            try {

                $imageResponse = $client->post('https://box.zapup.press/api/send/template',[
                    'headers'=>[
                        'Authorization'=>'Bearer '.$token,
                        'Content-Type'=>'application/json'
                    ],
                    'json'=>[
                        "phone"=>$phone,
                        "template"=>[
                            "name"=>"fixhrreport_image",
                            "language"=>[
                                "code"=>"en"
                            ],
                            "components"=>[
                                [
                                    "type"=>"header",
                                    "parameters"=>[
                                        [
                                            "type"=>"image",
                                            "image"=>[
                                                "link"=>"https://dev.fixhr.app/uploads/sendImagesVideos/Logo_668fbcc77b6c3702147360.png"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

                $this->info("Image template sent");
                $this->line($imageResponse->getBody());

                sleep(3);

                // $videoResponse = $client->post('https://box.zapup.press/api/send/template',[
                //     'headers'=>[
                //         'Authorization'=>'Bearer '.$token,
                //         'Content-Type'=>'application/json'
                //     ],
                //     'json'=>[
                //         "phone"=>$phone,
                //         "template"=>[
                //             "name"=>"fixhr_marketing_video",
                //             "language"=>[
                //                 "code"=>"en"
                //             ],
                //             "components"=>[
                //                 [
                //                     "type"=>"header",
                //                     "parameters"=>[
                //                         [
                //                             "type"=>"video",
                //                             "video"=>[
                //                                 "link"=>"https://dev.fixhr.app/uploads/sendImagesVideos/file_example_MP4_1280_10MG.mp4"
                //                             ]
                //                         ]
                //                     ]
                //                 ]
                //             ]
                //         ]
                //     ]
                // ]);

                // $this->info("Video template sent");
                // $this->line($videoResponse->getBody());

                // sleep(5);

            } catch (\Exception $e) {

                $this->error("Failed for ".$phone." : ".$e->getMessage());

            }
        }

        $this->info("Campaign Completed");

    }
}