<?php

declare(strict_types=1);

namespace Support;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Storage
{
    private static ?S3Client $client = null;
    private static string $bucket;

    public static function getClient(): S3Client
    {
        if (self::$client === null) {
            self::$bucket = getenv('MINIO_BUCKET') ?: 'resources';
            $endpoint = getenv('MINIO_ENDPOINT') ?: 'http://minio:9000';
            $accessKey = getenv('MINIO_ACCESS_KEY') ?: 'admin';
            $secretKey = getenv('MINIO_SECRET_KEY') ?: 'password123';

            self::$client = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);
            
            self::ensureBucketExists();
        }

        return self::$client;
    }

    private static function ensureBucketExists(): void
    {
        try {
            if (!self::$client->doesBucketExistV2(self::$bucket)) {
                self::$client->createBucket(['Bucket' => self::$bucket]);
            }
        } catch (AwsException $e) {
            // Log or handle exception in production
        }
    }

    public static function upload(string $key, string $filePath): bool
    {
        $client = self::getClient();
        try {
            $client->putObject([
                'Bucket' => self::$bucket,
                'Key'    => $key,
                'SourceFile' => $filePath,
            ]);
            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    public static function getDownloadUrl(string $key): string
    {
        try {
            // Create a separate client with the external endpoint for presigned URLs
            $externalEndpoint = getenv('MINIO_EXTERNAL_ENDPOINT') ?: 'http://localhost:9000';
            $accessKey = getenv('MINIO_ACCESS_KEY') ?: 'admin';
            $secretKey = getenv('MINIO_SECRET_KEY') ?: 'password123';
            $bucket = getenv('MINIO_BUCKET') ?: 'resources';
            
            $externalClient = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1',
                'endpoint' => $externalEndpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);
            
            $cmd = $externalClient->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => $key
            ]);
            
            $request = $externalClient->createPresignedRequest($cmd, '+60 minutes');
            return (string) $request->getUri();
        } catch (\Exception $e) {
            return '#';
        }
    }
}
