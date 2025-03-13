<?php

namespace App\Modules\CyberBuddy\Helpers;

use App\Modules\CyberBuddy\Events\ImportTable;
use App\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class TableStorage
{
    private static function StorageTypeFormString(string $storage): StorageType
    {
        foreach (StorageType::cases() as $storageType) {
            if ($storageType->value === $storage) {
                return $storageType;
            }
        }
        throw new InvalidParameterException("$storage is not a valid storage type.");
    }

    public static function credentialsFromOptions(array $options): array
    {
        Log::debug($options);
        $storage = TableStorage::StorageTypeFormString($options['storage']);
        switch ($storage) {
            case StorageType::AWS_S3:
                return self::credentialsFromOptionsS3($options);
            case StorageType::AZURE_BLOB_STORAGE:
                return self::credentialsFromOptionsAzure($options);
        }
    }

    private static function credentialsFromOptionsS3(array $options): array
    {
        return [
            'storage' => 's3',
            'region' => $options['region'],
            'access_key_id' => $options['access_key_id'],
            'secret_access_key' => $options['secret_access_key'],
            'input_folder' => $options['input_folder'],
            'output_folder' => $options['output_folder'],
        ];
    }

    private static function credentialsFromOptionsAzure(array $options): array
    {
        return [
            'storage' => 'azure',
            'connection_string' => $options['connection_string'],
            'input_folder' => $options['input_folder'],
            'output_folder' => $options['output_folder'],
        ];
    }

    public static function inDisk(array $credentials): Filesystem
    {
        $storage = TableStorage::StorageTypeFormString($credentials['storage']);
        switch ($storage) {
            case StorageType::AWS_S3:
                return self::inDiskS3($credentials);
            case StorageType::AZURE_BLOB_STORAGE:
                return self::inDiskAzure($credentials);
        }
    }

    private static function inDiskS3(array $credentials): Filesystem
    {
        $bucket = explode('/', $credentials['input_folder'], 2)[0];
        $prefix = explode('/', $credentials['input_folder'], 2)[1] ?? '';

        return Storage::build([
            'driver' => 's3',
            'key' => $credentials['access_key_id'],
            'secret' => $credentials['secret_access_key'],
            'region' => $credentials['region'],
            'bucket' => $bucket,
            'prefix' => $prefix,
        ]);
    }

    private static function inDiskAzure(array $credentials): Filesystem
    {
        $container = explode('/', $credentials['input_folder'], 2)[0];
        $prefix = explode('/', $credentials['input_folder'], 2)[1] ?? '';

        return Storage::build([
            'driver' => 'azure-storage-blob',
            'connection_string' => $credentials['connection_string'],
            'container' => $container,
            'prefix' => $prefix,
        ]);
    }

    public static function outDisk(array $credentials): Filesystem
    {
        $storage = TableStorage::StorageTypeFormString($credentials['storage']);
        switch ($storage) {
            case StorageType::AWS_S3:
                return self::outDiskS3($credentials);
            case StorageType::AZURE_BLOB_STORAGE:
                return self::outDiskAzure($credentials);
        }
    }

    private static function outDiskS3(array $credentials): Filesystem
    {
        $bucket = explode('/', $credentials['output_folder'], 2)[0];
        $prefix = explode('/', $credentials['output_folder'], 2)[1] ?? '';

        return Storage::build([
            'driver' => 's3',
            'key' => $credentials['access_key_id'],
            'secret' => $credentials['secret_access_key'],
            'region' => $credentials['region'],
            'bucket' => $bucket,
            'prefix' => $prefix,
        ]);
    }

    private static function outDiskAzure(array $credentials): Filesystem
    {
        $container = explode('/', $credentials['output_folder'], 2)[0];
        $prefix = explode('/', $credentials['output_folder'], 2)[1] ?? '';

        return Storage::build([
            'driver' => 'azure-storage-blob',
            'connection_string' => $credentials['connection_string'],
            'container' => $container,
            'prefix' => $prefix,
        ]);
    }

    public static function inClickhouseTableFunction(array $credentials, string $tableName): string
    {
        $storage = TableStorage::StorageTypeFormString($credentials['storage']);
        switch ($storage) {
            case StorageType::AWS_S3:
                return self::inClickhouseTableFunctionS3($credentials, $tableName);
            case StorageType::AZURE_BLOB_STORAGE:
                return self::inClickhouseTableFunctionAzure($credentials, $tableName);
        }
    }

    private static function inClickhouseTableFunctionS3(array $credentials, string $tableName): string
    {
        return "s3('https://s3.{$credentials['region']}.amazonaws.com/{$credentials['input_folder']}/$tableName', "
            . "'{$credentials['access_key_id']}', '{$credentials['secret_access_key']}', 'TabSeparatedWithNames')";
    }

    private static function inClickhouseTableFunctionAzure(array $credentials, string $tableName): string
    {
        $container = explode('/', $credentials['input_folder'], 2)[0];
        $prefix = explode('/', $credentials['input_folder'], 2)[1] ?? '';
        $prefix = Str::chopEnd($prefix, '/');
        return "azureBlobStorage('{$credentials['connection_string']}', '$container', "
            . "'$prefix/$tableName', 'TabSeparatedWithNames')";
    }

    public static function outClickhouseTableFunction(array $credentials, string $tableName, string $suffix = ''): string
    {
        $storage = TableStorage::StorageTypeFormString($credentials['storage']);
        switch ($storage) {
            case StorageType::AWS_S3:
                return self::outClickhouseTableFunctionS3($credentials, $tableName, $suffix);
            case StorageType::AZURE_BLOB_STORAGE:
                return self::outClickhouseTableFunctionAzure($credentials, $tableName, $suffix);
        }
    }

    private static function outClickhouseTableFunctionS3(array $credentials, string $tableName, string $suffix = ''): string
    {
        return "s3('https://s3.{$credentials['region']}.amazonaws.com/{$credentials['output_folder']}{$tableName}$suffix.parquet', "
            . "'{$credentials['access_key_id']}', '{$credentials['secret_access_key']}', 'Parquet')";
    }

    private static function outClickhouseTableFunctionAzure(array $credentials, string $tableName, string $suffix = ''): string
    {
        $container = explode('/', $credentials['output_folder'], 2)[0];
        $prefix = explode('/', $credentials['output_folder'], 2)[1] ?? '';
        $prefix = Str::chopEnd($prefix, '/');
        return "azureBlobStorage('{$credentials['connection_string']}', '$container', "
            . "'$prefix/{$tableName}$suffix.parquet', 'Parquet')";
    }

    public static function outClickhouseTableEngine(array $credentials, string $tableName, string $suffix = ''): string
    {
        $storage = TableStorage::StorageTypeFormString($credentials['storage']);
        switch ($storage) {
            case StorageType::AWS_S3:
                return self::outClickhouseTableEngineS3($credentials, $tableName, $suffix);
            case StorageType::AZURE_BLOB_STORAGE:
                return self::outClickhouseTableEngineAzure($credentials, $tableName, $suffix);
        }
    }

    private static function outClickhouseTableEngineS3(array $credentials, string $tableName, string $suffix = ''): string
    {
        return Str::replace('s3(', 'S3(', self::outClickhouseTableFunctionS3($credentials, $tableName, $suffix));
    }

    private static function outClickhouseTableEngineAzure(array $credentials, string $tableName, string $suffix = ''): string
    {
        return Str::replace('azureBlobStorage(', 'AzureBlobStorage(', self::outClickhouseTableFunctionAzure($credentials, $tableName, $suffix));
    }

    public static function dispatchImportTable(array $validated, User $user): int
    {
        $tables = collect($validated['tables'])->groupBy('table');
        $credentials = self::credentialsFromOptions($validated);
        foreach ($tables as $table => $columns) {
            Log::debug($table);
            ImportTable::dispatch($user, $credentials, $validated['copy'], $validated['deduplicate'], $validated['updatable'], $table, $columns->toArray(), $validated['description']);
        }
        return $tables->count();
    }

    public static function deleteOldOutFiles(array $credentials, string $tableName, int $filesToKeep = 3): void
    {
        $disk = self::outDisk($credentials);
        collect($disk->files())->filter(function ($file) use ($tableName) {
            return Str::startsWith($file, $tableName);
        })->sortByDesc(function ($file) use ($disk) {
            return $disk->lastModified($file);
        })->skip($filesToKeep)->each(function ($file) use ($disk) {
            $disk->delete($file);
        });
    }
}
