<?php

namespace Tests\Unit;

use App\Events\ImportTable;
use App\Helpers\StorageType;
use App\Helpers\TableStorage;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCaseNoDb;


class TableStorageTest extends TestCaseNoDb
{
    public function testStorageTypeFormString()
    {
        $this->assertEquals(StorageType::AWS_S3, TableStorage::StorageTypeFormString('s3'));
        $this->assertEquals(StorageType::AZURE_BLOB_STORAGE, TableStorage::StorageTypeFormString('azure'));
    }

    public function testCredentialsFromOptionsS3()
    {
        # Arrange
        $options = [
            'storage' => 's3',
            'region' => 'us-east-1',
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'input_folder' => 'input',
            'output_folder' => 'output',
        ];

        # Act
        $credentials = TableStorage::credentialsFromOptions($options);

        # Assert
        $this->assertEquals('s3', $credentials['storage']);
        $this->assertEquals('us-east-1', $credentials['region']);
        $this->assertEquals('test_key', $credentials['access_key_id']);
        $this->assertEquals('test_secret', $credentials['secret_access_key']);
        $this->assertEquals('input', $credentials['input_folder']);
        $this->assertEquals('output', $credentials['output_folder']);
    }

    public function testCredentialsFromOptionsAzure()
    {
        # Arrange
        $options = [
            'storage' => 'azure',
            'connection_string' => 'test_connection_string',
            'input_folder' => 'input',
            'output_folder' => 'output',
        ];

        # Act
        $credentials = TableStorage::credentialsFromOptions($options);

        # Assert
        $this->assertEquals('azure', $credentials['storage']);
        $this->assertEquals('test_connection_string', $credentials['connection_string']);
        $this->assertEquals('input', $credentials['input_folder']);
        $this->assertEquals('output', $credentials['output_folder']);
    }

    public static function inDiskProvider(): array
    {
        return [
            's3' => [[
                'storage' => 's3',
                'region' => 'us-east-1',
                'access_key_id' => 'test_key',
                'secret_access_key' => 'test_secret',
                'input_folder' => 'my_bucket/my_input_dir',
            ]],
            'azure' => [[
                'storage' => 'azure',
                'connection_string' => 'DefaultEndpointsProtocol=https;AccountName=my_account;AccountKey=my_key;EndpointSuffix=core.windows.net',
                'input_folder' => 'my_bucket/my_input_dir',
            ]],
        ];
    }

    #[DataProvider('inDiskProvider')]
    public function testInDisk(array $credentials)
    {
        # Act
        $disk = TableStorage::inDisk($credentials);

        # Assert
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
    }

    public static function outDiskProvider(): array
    {
        return [
            's3' => [[
                'storage' => 's3',
                'region' => 'us-east-1',
                'access_key_id' => 'test_key',
                'secret_access_key' => 'test_secret',
                'output_folder' => 'my_bucket/my_output_dir',
            ]],
            'azure' => [[
                'storage' => 'azure',
                'connection_string' => 'DefaultEndpointsProtocol=https;AccountName=my_account;AccountKey=my_key;EndpointSuffix=core.windows.net',
                'output_folder' => 'my_bucket/my_output_dir',
            ]],
        ];
    }

    #[DataProvider('outDiskProvider')]
    public function testOutDisk(array $credentials)
    {
        # Act
        $disk = TableStorage::outDisk($credentials);

        # Assert
        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
    }

    public static function clickhouseTableFunctionOrEngineProvider(): array
    {
        return [
            's3' => [
                [
                    'storage' => 's3',
                    'region' => 'us-east-1',
                    'access_key_id' => 'test_key',
                    'secret_access_key' => 'test_secret',
                    'input_folder' => 'my_input_bucket/my_input_dir',
                    'output_folder' => 'my_output_bucket/my_output_dir',
                ],
                'test_table',
                '_uid',
                "s3('https://s3.us-east-1.amazonaws.com/my_input_bucket/my_input_dir/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "S3('https://s3.us-east-1.amazonaws.com/my_input_bucket/my_input_dir/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "s3('https://s3.us-east-1.amazonaws.com/my_output_bucket/my_output_dir/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
                "S3('https://s3.us-east-1.amazonaws.com/my_output_bucket/my_output_dir/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
            ],
            's3 trailing slash' => [
                [
                    'storage' => 's3',
                    'region' => 'us-east-1',
                    'access_key_id' => 'test_key',
                    'secret_access_key' => 'test_secret',
                    'input_folder' => 'my_input_bucket/my_input_dir/',
                    'output_folder' => 'my_output_bucket/my_output_dir/',
                ],
                'test_table',
                '_uid',
                "s3('https://s3.us-east-1.amazonaws.com/my_input_bucket/my_input_dir/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "S3('https://s3.us-east-1.amazonaws.com/my_input_bucket/my_input_dir/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "s3('https://s3.us-east-1.amazonaws.com/my_output_bucket/my_output_dir/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
                "S3('https://s3.us-east-1.amazonaws.com/my_output_bucket/my_output_dir/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
            ],
            's3 bucket root' => [
                [
                    'storage' => 's3',
                    'region' => 'us-east-1',
                    'access_key_id' => 'test_key',
                    'secret_access_key' => 'test_secret',
                    'input_folder' => 'my_input_bucket',
                    'output_folder' => 'my_output_bucket',
                ],
                'test_table',
                '_uid',
                "s3('https://s3.us-east-1.amazonaws.com/my_input_bucket/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "S3('https://s3.us-east-1.amazonaws.com/my_input_bucket/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "s3('https://s3.us-east-1.amazonaws.com/my_output_bucket/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
                "S3('https://s3.us-east-1.amazonaws.com/my_output_bucket/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
            ],
            's3 bucket root trailing slash' => [
                [
                    'storage' => 's3',
                    'region' => 'us-east-1',
                    'access_key_id' => 'test_key',
                    'secret_access_key' => 'test_secret',
                    'input_folder' => 'my_input_bucket/',
                    'output_folder' => 'my_output_bucket/',
                ],
                'test_table',
                '_uid',
                "s3('https://s3.us-east-1.amazonaws.com/my_input_bucket/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "S3('https://s3.us-east-1.amazonaws.com/my_input_bucket/test_table', 'test_key', 'test_secret', 'TabSeparatedWithNames')",
                "s3('https://s3.us-east-1.amazonaws.com/my_output_bucket/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
                "S3('https://s3.us-east-1.amazonaws.com/my_output_bucket/test_table_uid.parquet', 'test_key', 'test_secret', 'Parquet')",
            ],
            'azure' => [
                [
                    'storage' => 'azure',
                    'connection_string' => 'azure_connexion_string',
                    'input_folder' => 'my_input_bucket/my_input_dir',
                    'output_folder' => 'my_output_bucket/my_output_dir',
                ],
                'test_table',
                '_uid',
                "azureBlobStorage('azure_connexion_string', 'my_input_bucket', 'my_input_dir/test_table', 'TabSeparatedWithNames')",
                "AzureBlobStorage('azure_connexion_string', 'my_input_bucket', 'my_input_dir/test_table', 'TabSeparatedWithNames')",
                "azureBlobStorage('azure_connexion_string', 'my_output_bucket', 'my_output_dir/test_table_uid.parquet', 'Parquet')",
                "AzureBlobStorage('azure_connexion_string', 'my_output_bucket', 'my_output_dir/test_table_uid.parquet', 'Parquet')",
            ],
            'azure trailing slash' => [
                [
                    'storage' => 'azure',
                    'connection_string' => 'azure_connexion_string',
                    'input_folder' => 'my_input_bucket/my_input_dir/',
                    'output_folder' => 'my_output_bucket/my_output_dir/',
                ],
                'test_table',
                '_uid',
                "azureBlobStorage('azure_connexion_string', 'my_input_bucket', 'my_input_dir/test_table', 'TabSeparatedWithNames')",
                "AzureBlobStorage('azure_connexion_string', 'my_input_bucket', 'my_input_dir/test_table', 'TabSeparatedWithNames')",
                "azureBlobStorage('azure_connexion_string', 'my_output_bucket', 'my_output_dir/test_table_uid.parquet', 'Parquet')",
                "AzureBlobStorage('azure_connexion_string', 'my_output_bucket', 'my_output_dir/test_table_uid.parquet', 'Parquet')",
            ],
            'azure bucket root' => [
                [
                    'storage' => 'azure',
                    'connection_string' => 'azure_connexion_string',
                    'input_folder' => 'my_input_bucket',
                    'output_folder' => 'my_output_bucket',
                ],
                'test_table',
                '_uid',
                "azureBlobStorage('azure_connexion_string', 'my_input_bucket', 'test_table', 'TabSeparatedWithNames')",
                "AzureBlobStorage('azure_connexion_string', 'my_input_bucket', 'test_table', 'TabSeparatedWithNames')",
                "azureBlobStorage('azure_connexion_string', 'my_output_bucket', 'test_table_uid.parquet', 'Parquet')",
                "AzureBlobStorage('azure_connexion_string', 'my_output_bucket', 'test_table_uid.parquet', 'Parquet')",
            ],
            'azure bucket root trailing slash' => [
                [
                    'storage' => 'azure',
                    'connection_string' => 'azure_connexion_string',
                    'input_folder' => 'my_input_bucket/',
                    'output_folder' => 'my_output_bucket/',
                ],
                'test_table',
                '_uid',
                "azureBlobStorage('azure_connexion_string', 'my_input_bucket', 'test_table', 'TabSeparatedWithNames')",
                "AzureBlobStorage('azure_connexion_string', 'my_input_bucket', 'test_table', 'TabSeparatedWithNames')",
                "azureBlobStorage('azure_connexion_string', 'my_output_bucket', 'test_table_uid.parquet', 'Parquet')",
                "AzureBlobStorage('azure_connexion_string', 'my_output_bucket', 'test_table_uid.parquet', 'Parquet')",
            ],
        ];
    }

    #[DataProvider('clickhouseTableFunctionOrEngineProvider')]
    public function testInClickhouseTableFunction(array $credentials, string $tableName, string $suffix, string $expectedInFunction): void
    {
        # Act
        $result = TableStorage::inClickhouseTableFunction($credentials, $tableName);

        # Assert
        $this->assertEquals($expectedInFunction, $result);
    }

    #[DataProvider('clickhouseTableFunctionOrEngineProvider')]
    public function testOutClickhouseTableFunction(array $credentials, string $tableName, string $suffix, $unused1, $unused2, string $expectedOutFunction): void
    {
        # Act
        $result = TableStorage::outClickhouseTableFunction($credentials, $tableName, $suffix);

        # Assert
        $this->assertEquals($expectedOutFunction, $result);
    }

    #[DataProvider('clickhouseTableFunctionOrEngineProvider')]
    public function testOutClickhouseTableEngine(array $credentials, string $tableName, string $suffix, $unused1, $unused2, $unused3, string $expectedOutEngine): void
    {
        # Act
        $result = TableStorage::outClickhouseTableEngine($credentials, $tableName, $suffix);

        # Assert
        $this->assertEquals($expectedOutEngine, $result);
    }

    public function testDispatchImportTable()
    {
        # Arrange
        $validated = [
            'storage' => 's3',
            'region' => 'us-east-1',
            'access_key_id' => 'test_key',
            'secret_access_key' => 'test_secret',
            'input_folder' => 'input',
            'output_folder' => 'output',
            'tables' => [
                ['table' => 'table1', 'columns' => ['col1', 'col2']],
                ['table' => 'table2', 'columns' => ['col3', 'col4']],
            ],
            'copy' => true,
            'deduplicate' => false,
            'updatable' => true,
            'description' => 'test description',
        ];
        $user = new User();
        Event::fake();

        # Act
        $result = TableStorage::dispatchImportTable($validated, $user);

        # Assert
        $this->assertEquals(2, $result);
        Event::assertDispatched(ImportTable::class, 2);
    }
}
