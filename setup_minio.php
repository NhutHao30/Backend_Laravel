<?php
require __DIR__.'/vendor/autoload.php';

use Aws\S3\S3Client;

try {
    $client = new S3Client([
        'version' => 'latest',
        'region'  => 'us-east-1',
        'endpoint' => 'http://127.0.0.1:9000',
        'use_path_style_endpoint' => true,
        'credentials' => [
            'key'    => 'admin',
            'secret' => 'password123',
        ],
    ]);

    $client->putBucketPolicy([
        'Bucket' => 'dolabakery',
        'Policy' => json_encode([
            'Version' => '2012-10-17',
            'Statement' => [
                [
                    'Action' => ['s3:GetObject'],
                    'Effect' => 'Allow',
                    'Principal' => '*',
                    'Resource' => ['arn:aws:s3:::dolabakery/*'],
                ],
            ],
        ]),
    ]);

    echo "MinIO Bucket 'dolabakery' da duoc mo cong Public thanh cong!\n";
} catch (Exception $e) {
    echo "Loi: " . $e->getMessage() . "\n";
}
