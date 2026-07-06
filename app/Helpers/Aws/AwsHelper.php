<?php

namespace App\Helpers\Aws;

use App\Helpers\CentralLogics;
use App\Models\ApiCredential;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Cache;


class AwsHelper
{
    protected $s3;

    public function __construct()
    {
        $cache_key = hash('sha256', 'aws_creds');
        $record = Cache::remember($cache_key, now()->addHours(24), function () {
            return ApiCredential::where(['apc_type'=>'aws','apc_is_active'=>1])->first();
        });

        if($record){
            try {
                // $key = CentralLogics::encrypt_or_decrypt($record->apc_key,'decrypt');
                // $secret = CentralLogics::encrypt_or_decrypt($record->apc_secret,'decrypt');
                $key = 'AKIASFUIR2SWNPMOLWN4';
                $secret = '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';
                if($key && $secret){
                    $this->s3 = new S3Client([
                        'version'     => $record->apc_version ?? 'latest',
                        'region'      => $record->apc_region ?? 'us-east-1',
                        'credentials' => [
                            'key'    => $key,
                            'secret' => $secret,
                        ]
                    ]);
                } else {
                    \Log::error("AWS credentials are null after decryption.");
                }

            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $this->s3 = null;
                \Log::error("Decryption failed for AWS credentials: " . $e->getMessage());
            }
        } else {
            $this->s3 = null;
            \Log::error("No active AWS credentials found in DB.");
        }
    }

    // public function uploadFileToS3(string $bucket, string $imagePath, $file)
    // {
    //     if(!$this->s3){
    //         \Log::error("S3 client not initialized. File: ".$file->getClientOriginalName());
    //         return ['status' => false, 'message' => 'S3 client not initialized'];
    //     }

    //     try {
    //         // Proper MIME type
    //         $mimeType = $file->getMimeType() ?? 'image/jpeg';
    //         $ext = $file->getClientOriginalExtension();

    //         dd($mimeType, $ext, $this->s3);

    //         // Use realPath() to open the file
    //         $result = $this->s3->putObject([
    //             'Bucket' => $bucket,
    //             'Key'    => $imagePath,
    //             'ContentType' => $ext,
    //             'ContentDisposition' => 'inline',
    //             'Body'   => fopen($file->getRealPath(), 'r'),
    //         ]);

    //         return ['status' => true, 'ObjectURL' => $result['ObjectURL'] ?? null];

    //     } catch (S3Exception $e) {
    //         \Log::error("S3 upload failed: ".$e->getMessage());
    //         return ['status' => false, 'message' => $e->getMessage()];
    //     }
    // }

    public function uploadFileToS3(string $bucket, string $imagePath, $file)
    {
        if (!$this->s3) {
            \Log::error("S3 client not initialized. File: " . $file->getClientOriginalName());
            return ['status' => false, 'message' => 'S3 client not initialized'];
        }

        try {
            $mimeType = $file->getMimeType() ?? 'image/jpeg';

            $result = $this->s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $imagePath,
                'ContentType' => $mimeType, // ❗ ext nahi, mime type use karo
                'ContentDisposition' => 'inline',
                'Body'   => file_get_contents($file), // ✅ BEST
            ]);

            return [
                'status' => true,
                'ObjectURL' => $result['ObjectURL'] ?? null
            ];

        } catch (S3Exception $e) {
            \Log::error("S3 upload failed: " . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function getObjectFromS3(string $bucket, string $objectKey)
    {
        try {
            $result = $this->s3->getObject([
                'Bucket' => $bucket,
                'Key'    => $objectKey,
            ]);
            return $result['Body']->getContents();
        } catch (S3Exception $e) {
            return null;
        }
    }

    public function deleteFileFromS3(string $bucket, string $objectKey)
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $bucket,
                'Key'    => $objectKey,
            ]);

            return ['status' => true, 'message' => 'File deleted successfully'];
        } catch (S3Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
