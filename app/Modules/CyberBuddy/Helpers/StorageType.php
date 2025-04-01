<?php

namespace App\Modules\CyberBuddy\Helpers;

// TODO : move to App\Modules\CyberBuddy\Enums
enum StorageType: string
{
    case AWS_S3 = 's3';
    case AZURE_BLOB_STORAGE = 'azure';
}
