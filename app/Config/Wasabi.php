<?php

namespace Config;

use Aws\S3\S3Client;

class Wasabi
{
    public static function createClient()
    {
        return new S3Client([
            'version'     => 'latest',
            'region'      => 'us-central-1',
            'endpoint'    => 'https://s3.us-central-1.wasabisys.com',
            'credentials' => [
                'key'    => 'E6HDB33BYOIRC46OZPJ9',
                'secret' => 'uy4i8rLle2RiSIzQzwNWn0iZvpSa5a6ZgooiZooB',
            ],
            'use_path_style_endpoint' => true,
        ]);
    }
}
