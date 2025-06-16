<?php

namespace App\Enums;

enum StorageType: string
{
    case AWS_S3 = 's3';
    case AZURE_BLOB_STORAGE = 'azure';
}
