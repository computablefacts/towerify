<?php

namespace App\Modules\Federa\Http\Controllers;

use App\Modules\Federa\Events\IngestFile;
use App\Modules\Federa\Http\Requests\DownloadOneFileRequest;
use App\Modules\Federa\Http\Requests\StreamOneFileRequest;
use App\Modules\Federa\Http\Requests\UploadManyFilesRequest;
use App\Modules\Federa\Http\Requests\UploadOneFileRequest;
use App\Modules\Federa\Models\CsvFile;
use App\Modules\Federa\Rules\IsValidCollectionName;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FederaController extends Controller
{
    public function __construct()
    {
        //
    }

    public function deleteCollection(int $id)
    {
        \App\Modules\Federa\Models\Collection::where('id', $id)->update(['is_deleted' => true]);
        return response()->json([
            'success' => __('The collection will be deleted soon!'),
        ]);
    }

    public function deleteFile(int $id)
    {
        CsvFile::where('id', $id)->update(['is_deleted' => true]);
        return response()->json([
            'success' => __('The file will be deleted soon!'),
        ]);
    }

    public function collections()
    {
        return \App\Modules\Federa\Models\Collection::orderBy('name', 'asc')
            ->get()
            ->map(function (\App\Modules\Federa\Models\Collection $collection) {
                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
                ];
            });
    }

    public function streamFile(string $secret, StreamOneFileRequest $request)
    {
        /** @var CsvFile $file */
        $file = CsvFile::where('secret', $secret)->where('is_deleted', false)->first();

        if (!$file) {
            return response()->json(['error' => 'Unknown file.'], 500);
        }

        /** @var \App\Modules\Federa\Models\Collection $collection */
        $collection = $file->collection()->where('is_deleted', false)->first();

        if (!$collection) {
            return response()->json(['error' => 'Unknown collection.'], 500);
        }

        $storage = Storage::disk('files-s3');
        $path = $this->storagePath($collection, $file);

        if (!$storage->exists($path)) {
            return response()->json(['error' => 'Unknown storage path.'], 500);
        }
        return $storage->response($path, null, [
            'pragma' => 'private',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function downloadFile(string $secret, DownloadOneFileRequest $request)
    {
        /** @var CsvFile $file */
        $file = CsvFile::where('secret', $secret)->where('is_deleted', false)->first();

        if (!$file) {
            return response()->json(['error' => 'Unknown file.'], 500);
        }

        /** @var \App\Modules\Federa\Models\Collection $collection */
        $collection = $file->collection()->where('is_deleted', false)->first();

        if (!$collection) {
            return response()->json(['error' => 'Unknown collection.'], 500);
        }

        $storage = Storage::disk('files-s3');
        $path = $this->storagePath($collection, $file);

        if (!$storage->exists($path)) {
            return response()->json(['error' => 'Unknown storage path.'], 500);
        }

        $rawFile = $this->storageFileName($file);

        if (file_exists("/tmp/{$rawFile}")) { // bypass S3 if possible
            return response()->download("/tmp/{$rawFile}", null, [
                'pragma' => 'private',
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }
        return $storage->download($path, null, [
            'pragma' => 'private',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function uploadOneFile(UploadOneFileRequest $request)
    {
        /** @var \App\Modules\Federa\Models\Collection $collection */
        $collection = \App\Modules\Federa\Models\Collection::where('name', $request->string('collection'))->where('is_deleted', false)->first();

        if (!$collection) {
            if (!IsValidCollectionName::test($request->string('collection'))) {
                return response()->json(['error' => 'Invalid collection name.'], 500);
            }
            /** @var \App\Modules\Federa\Models\Collection $collection */
            $collection = \App\Modules\Federa\Models\Collection::create(['name' => $request->string('collection')]);
        }
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'Missing file content.'], 500);
        }

        $file = $request->file('file');
        $hasHeaders = $request->has('has_headers', false);
        $url = $this->saveOneFile($collection, $file, $hasHeaders);

        if ($url) {
            return response()->json([
                'success' => 'The file has been saved and will be processed soon.',
                'url' => $url,
            ]);
        }
        return response()->json(['error' => 'The file could not be saved.'], 500);
    }

    public function uploadManyFiles(UploadManyFilesRequest $request)
    {
        /** @var \App\Modules\Federa\Models\Collection $collection */
        $collection = \App\Modules\Federa\Models\Collection::where('name', $request->string('collection'))->where('is_deleted', false)->first();

        if (!$collection) {
            if (!IsValidCollectionName::test($request->string('collection'))) {
                return response()->json(['error' => 'Invalid collection name.'], 500);
            }
            /** @var \App\Modules\Federa\Models\Collection $collection */
            $collection = \App\Modules\Federa\Models\Collection::create(['name' => $request->string('collection')]);
        }

        $files = $request->allFiles();
        $hasHeaders = $request->has('has_headers', false);
        $successes = [];
        $errors = [];

        foreach ($files['files'] as $file) {
            $url = $this->saveOneFile($collection, $file, $hasHeaders);
            if ($url) {
                $successes[] = $url;
            } else {
                $errors[] = $file->getClientOriginalName();
            }
        }
        if (count($errors) <= 0) {
            return response()->json([
                'success' => 'All files have been saved and will be processed soon.',
                'urls' => $successes,
            ]);
        }
        return response()->json([
            'error' => 'One or more files could not be saved.',
            'urls_success' => $successes,
            'filenames_error' => $errors,
        ], 500);
    }

    private function saveOneFile(\App\Modules\Federa\Models\Collection $collection, UploadedFile $file, bool $hasHeaders): ?string
    {
        // Extract file metadata
        $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $file_extension = $file->getClientOriginalExtension();
        $file_path = $file->getClientOriginalPath();
        $file_size = $file->getSize();
        $file_md5 = md5_file($file->getRealPath());
        $file_sha1 = sha1_file($file->getRealPath());
        $mime_type = $file->getClientMimeType();


        // Normalize filename
        $file_name_normalized = strtolower(trim($file_name));
        $file_name_normalized = preg_replace('/[\s\-]+/', '-', $file_name_normalized);
        $file_name_normalized = preg_replace('/[^-a-z0-9._]+/', '', $file_name_normalized);

        /** @var CsvFile $fileRef */
        $fileRef = $collection->files()->create([
            'name' => $file_name,
            'name_normalized' => $file_name_normalized,
            'extension' => $file_extension,
            'path' => $file_path,
            'size' => $file_size,
            'md5' => $file_md5,
            'sha1' => $file_sha1,
            'mime_type' => $mime_type,
            'secret' => Str::random(32),
            'has_headers' => $hasHeaders,
            'created_by' => Auth::user()->id,
        ]);

        // Copy file to S3
        $storage = Storage::disk('files-s3');
        $filepath = $this->storageFilePath($collection);
        $filename = $this->storageFileName($fileRef);

        if (!$storage->exists($filepath)) {
            if (!$storage->makeDirectory($filepath)) {
                $fileRef->delete();
                return null;
            }
        }
        if (!$storage->putFileAs($filepath, $file, $filename)) {
            $fileRef->delete();
            return null;
        }

        // Process file ex. create embeddings
        event(new IngestFile(Auth::user(), $collection->name, $fileRef->id));

        return $fileRef->downloadUrl();
    }

    private function storagePath(\App\Modules\Federa\Models\Collection $collection, CsvFile $file): string
    {
        return "{$this->storageFilePath($collection)}/{$this->storageFileName($file)}";
    }

    private function storageFilePath(\App\Modules\Federa\Models\Collection $collection): string
    {
        return "/federa/{$collection->id}";
    }

    private function storageFileName(CsvFile $file): string
    {
        return "{$file->id}_{$file->name_normalized}.{$file->extension}";
    }
}