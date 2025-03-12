<?php

namespace App\Modules\CyberBuddy\Helpers;

enum StorageType: string
{
    case AWS_S3 = 's3';
    case AZURE_BLOB_STORAGE = 'azure';
}
