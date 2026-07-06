<?php

namespace App\Services;

use App\Helpers\CentralLogics;
use App\Models\ApiCallLog;
use Aws\Rekognition\RekognitionClient;
use App\Helpers\Aws\AwsHelper;
use App\Models\ApiCredential;
use Illuminate\Support\Facades\Cache;

class FaceRekognitionService
{
    protected $rekognition;
    protected $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        $this->awsHelper = $awsHelper;

        $cache_key = hash('sha256', 'aws_creds');
        $record = Cache::remember($cache_key, now()->addHours(24), function () {
            return ApiCredential::where(['apc_type'=>'aws','apc_is_active'=>1])->first();
        });

        try {
            if($record){
                $key = CentralLogics::encrypt_or_decrypt($record->apc_key,'decrypt');
                $secret =  CentralLogics::encrypt_or_decrypt($record->apc_secret,'decrypt');
                $this->rekognition = new RekognitionClient([
                    'version'     => $record->apc_version,
                    'region'      => $record->apc_region,
                    'credentials' => [
                        'key'    => $key,
                        'secret' => $secret,
                    ]
                ]);
            }
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            \Log::error("Decryption failed for AWS credentials: " . $e->getMessage());
        }

    }

    public function uploadAndAuthenticate(string $bId, string $bucket, $file, string $imageKey): array
    {
        $imagePath = $bId . '/' . $imageKey;
        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);

        if ($uploadResult['status']) {
            return $this->recognizeFace($bId, $bucket, $imagePath, $uploadResult['ObjectURL']);
        }
        return ['status' => false, 'message' => 'Upload Failed'];
    }

    public function indexFaces(string $bucket, string $imageKey, string $collectionId): array
    {
        try {
            return $this->rekognition->indexFaces([
                'CollectionId'       => $collectionId,
                'Image'              => ['S3Object' => ['Bucket' => $bucket, 'Name' => $imageKey]],
                'ExternalImageId'    => pathinfo($imageKey, PATHINFO_FILENAME),
                'DetectionAttributes' => ['ALL'],
            ])->toArray();
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function createLivenessSession(string $bucket): array
    {
        try {
            $result = $this->rekognition->createFaceLivenessSession([
                'Settings' => ['OutputConfig' => ['S3Bucket' => $bucket]]
            ]);

            return ['status' => isset($result['SessionId']), 'message' => 'Session Created', 'result' => $result['SessionId'] ?? null];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function recognizeFace($bId, string $bucket, string $objectKey, string $s3Url): array
    {
        $imageBytes = $this->awsHelper->getObjectFromS3($bucket, $objectKey);
        if (!$imageBytes || strlen($imageBytes) < 1000) {
            return ['status' => false, 'message' => 'Image is empty or invalid'];
        }

        try {
            $response = $this->rekognition->searchFacesByImage([
                'CollectionId' => 'fixhr_employees',
                'Image'        => ['Bytes' => $imageBytes],
            ]);

            $cache_key = hash('sha256', 'aws_creds');
            $record = Cache::remember($cache_key, now()->addHours(24), function () {
                return ApiCredential::where(['apc_type'=>'aws','apc_is_active'=>1])->first();
            });

            if($record){
                $apiCallLog = ApiCallLog::where('acl_b_id', $bId)->where('acl_apc_id', $record->apc_id)->first();
                if ($apiCallLog) {
                    $apiCallLog->acl_count = $apiCallLog->acl_count+1;
                    $apiCallLog->save();
                } else {
                    ApiCallLog::create([
                        'acl_b_id' => $bId,
                        'acl_apc_id' => $record->apc_id,
                        'acl_count' => 1,
                    ]);
                }
            }

            if (!empty($response['FaceMatches'])) {
                return [
                    'status'  => true,
                    'message' => 'Person Found',
                    'result'  => ['faceId' => $response['FaceMatches'][0]['Face']['FaceId'], 'image_url' => $s3Url]
                ];
            }
            return ['status' => false, 'message' => 'Person Not Authorized'];
        } catch (\Aws\Exception\AwsException $e) {
            // AWS-specific error
            return ['status' => false, 'message' => $e->getAwsErrorMessage()];
        } catch (\Exception $e) {
            // Generic error
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
