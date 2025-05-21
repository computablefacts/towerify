<?php

namespace App\Http\Controllers;

use App\Conversations\QuestionsAndAnswers;
use App\Events\ImportVirtualTable;
use App\Events\IngestFile;
use App\Helpers\ApiUtilsFacade as ApiUtils;
use App\Helpers\ClickhouseClient;
use App\Helpers\ClickhouseLocal;
use App\Helpers\ClickhouseUtils;
use App\Helpers\OpenAi;
use App\Helpers\StorageType;
use App\Helpers\TableStorage;
use App\Http\Requests\DownloadOneFileRequest;
use App\Http\Requests\StreamOneFileRequest;
use App\Http\Requests\UploadManyFilesRequest;
use App\Http\Requests\UploadOneFileRequest;
use App\Models\Chunk;
use App\Models\Conversation;
use App\Models\File;
use App\Models\Prompt;
use App\Models\Table;
use App\Models\Template;
use App\Models\YnhFramework;
use App\Models\YnhServer;
use App\Rules\IsValidCollectionName;
use App\User;
use BotMan\BotMan\BotMan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToRetrieveMetadata;
use Symfony\Component\Process\Process;

/** @deprecated */
class CyberBuddyController extends Controller
{
    public function __construct()
    {
        //
    }

    public static function enhanceAnswerWithSources(string $answer, Collection $sources): string
    {
        $matches = [];
        // Extract: [12] from [[12]] or [[12] and [13]] from [[12],[13]]
        $isOk = preg_match_all("/\[\[\d+]]|\[\[\d+]|\[\d+]]/", $answer, $matches);
        if (!$isOk) {
            return Str::replace(["\n\n", "\n-"], "<br>", $answer);
        }
        $references = [];
        /** @var array $refs */
        $refs = $matches[0];
        foreach ($refs as $ref) {
            $id = Str::replace(['[', ']'], '', $ref);
            /** @var array $tooltip */
            $tooltip = $sources->filter(fn($ctx) => $ctx['id'] == $id)->first();
            /** @var Chunk $chunk */
            $chunk = Chunk::find($id);
            /** @var File $file */
            $file = $chunk?->file()->first();
            $src = $file ? "<a href=\"{$file->downloadUrl()}\" style=\"text-decoration:none;color:black\">{$file->name_normalized}.{$file->extension}</a>, p. {$chunk->page}" : "";
            if ($tooltip) {
                if (Str::startsWith($tooltip['text'], 'ESSENTIAL DIRECTIVE')) {
                    $color = '#1DD288';
                } else if (Str::startsWith($tooltip['text'], 'STANDARD DIRECTIVE')) {
                    $color = '#C5C3C3';
                } else if (Str::startsWith($tooltip['text'], 'ADVANCED DIRECTIVE')) {
                    $color = '#FDC99D';
                } else {
                    $color = '#F8B500';
                }
                $answer = Str::replace($ref, "<b style=\"color:{$color}\">[{$id}]</b>", $answer);
                $references[$id] = "
                  <li style=\"padding:0;margin-bottom:0.25rem\">
                    <b style=\"color:{$color}\">[{$id}]</b>&nbsp;
                    <div class=\"cb-tooltip-list\">
                      {$src}
                      <span class=\"cb-tooltiptext cb-tooltip-list-top\" style=\"background-color:{$color};color:#444;\">
                        {$tooltip['text']}
                      </span>
                    </div>
                  </li>
                ";
            }
        }
        ksort($references);
        $answer = "{$answer}<br><br><b>Sources :</b><ul style=\"padding:0\">" . collect($references)->values()->join("") . "</ul>";
        return Str::replace(["\n\n", "\n-"], "<br>", $answer);
    }

    public static function removeSourcesFromAnswer(string $answer): string
    {
        // Remove sources such as [[12]] or [[12],[13]] from the answer
        return preg_replace("/\[((\[\d+],?)+)]/", "", $answer);
    }

    public static function saveDistantFile(\App\Models\Collection $collection, string $url, bool $triggerIngest = true, bool $test = false): ?string
    {
        $content = file_get_contents($url);

        if ($content === false) {
            return null;
        }

        $filename = pathinfo(Str::afterLast($url, '/'), PATHINFO_FILENAME);
        $extension = pathinfo(Str::afterLast($url, '/'), PATHINFO_EXTENSION);
        $name_normalized = strtolower(trim($filename));
        $path = "/tmp/{$name_normalized}.{$extension}";
        file_put_contents($path, $content);

        return self::saveLocalFile($collection, $path, $triggerIngest, $test);
    }

    public static function saveLocalFile(\App\Models\Collection $collection, string $path, bool $triggerIngest = true, bool $test = false): ?string
    {
        $name = \Illuminate\Support\Facades\File::name($path);
        $extension = \Illuminate\Support\Facades\File::extension($path);
        $originalName = "{$name}.{$extension}";
        $mimeType = \Illuminate\Support\Facades\File::mimeType($path);
        $error = null;
        $uploadedFile = new UploadedFile($path, $originalName, $mimeType, $error, $test);
        return self::saveUploadedFile($collection, $uploadedFile, $triggerIngest);
    }

    public static function saveUploadedFile(\App\Models\Collection $collection, UploadedFile $file, bool $triggerIngest = true): ?string
    {
        $file_md5 = md5_file($file->getRealPath());
        $file_sha1 = sha1_file($file->getRealPath());
        /** @var ?File $file_exists */
        $file_exists = $collection->files()->where('md5', $file_md5)->where('sha1', $file_sha1)->first();

        if ($file_exists) { // ensure each file is added only once to a given collection
            return $file_exists->downloadUrl();
        }

        // Extract file metadata
        $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $file_extension = $file->getClientOriginalExtension();
        $file_path = $file->getClientOriginalPath();
        $file_size = $file->getSize();
        $mime_type = $file->getClientMimeType();

        if ($file_extension === 'jsonl' && $mime_type === 'application/octet-stream') {
            $mime_type = 'application/x-ndjason';
        }

        // Normalize filename
        $file_name_normalized = strtolower(trim($file_name));
        $file_name_normalized = preg_replace('/[\s\-]+/', '-', $file_name_normalized);
        $file_name_normalized = preg_replace('/[^-a-z0-9._]+/', '', $file_name_normalized);

        /** @var File $fileRef */
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
            'created_by' => Auth::user()->id,
        ]);

        // Copy file to S3
        $storage = Storage::disk('files-s3');
        $filepath = self::storageFilePath($collection);
        $filename = self::storageFileName($fileRef);

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
        if ($triggerIngest) {
            IngestFile::dispatch(Auth::user(), $collection->name, $fileRef->id);
        }
        return $fileRef->downloadUrl();
    }

    private static function storageFilePath(\App\Models\Collection $collection): string
    {
        return "/cyber-buddy/{$collection->id}";
    }

    private static function storageFileName(File $file): string
    {
        return "{$file->id}_{$file->name_normalized}.{$file->extension}";
    }

    public function showPage()
    {
        return view('modules.cyber-buddy.page');
    }

    public function showChat()
    {
        return view('modules.cyber-buddy.chat');
    }

    public function templates()
    {
        return Template::where('readonly', true)
            ->orderBy('name', 'asc')
            ->get()
            ->concat(
                Template::where('readonly', false)
                    ->where('created_by', Auth::user()->id)
                    ->orderBy('name', 'asc')
                    ->get()
            )
            ->map(function (Template $template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'template' => $template->template,
                    'type' => $template->readonly ? 'template' : 'draft',
                    'user' => User::find($template->created_by)->name,
                ];
            });
    }

    public function saveTemplate(Request $request)
    {
        // TODO : validate request
        $id = $request->integer('id', 0);
        $name = $request->string('name', '');
        $blocks = $request->input('template', []);
        $model = $request->boolean('is_model', false);

        if (isset($blocks) && count($blocks) > 0) {
            if ($id === 0) {
                $template = Template::create([
                    'name' => Str::replace('v', '', $name),
                    'template' => $blocks,
                    'readonly' => $model,
                ]);
            } else {
                $template = Template::where('id', $id)->where('readonly', false)->first();
                $version = ($template && Str::contains($template->name, 'v') ? (int)Str::afterLast($template->name, 'v') : 0) + 1;
                if ($template) {
                    $template->name = Str::beforeLast($template->name, 'v') . "v{$version}";
                    $template->template = $blocks;
                    $template->save();
                } else {
                    $userId = Auth::user()->id;
                    $template = Template::create([
                        'name' => "{$name} u{$userId}v1",
                        'template' => $blocks,
                        'readonly' => false,
                    ]);
                }
            }
            return [
                'id' => $template->id,
                'name' => $template->name,
                'template' => $template->template,
                'type' => $template->readonly ? 'template' : 'draft',
                'user' => User::find($template->created_by)->name,
            ];
        }
        return [];
    }

    public function deleteTemplate(int $id)
    {
        Template::where('id', $id)->where('readonly', false)->delete();
        return response()->json([
            'success' => __('The template has been deleted!'),
        ]);
    }

    public function collections()
    {
        return \App\Models\Collection::orderBy('name', 'asc')
            ->get()
            ->map(function (\App\Models\Collection $collection) {
                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
                ];
            });
    }

    public function llm1(Request $request)
    {
        // TODO : validate request
        $collection = $request->string('collection');
        $prompt = $request->string('prompt');
        $response = ApiUtils::ask_chunks($prompt, $collection, null, true, true, 'fr', 10, true);
        if (!isset($response['error']) || $response['error']) {
            return 'Une erreur s\'est produite. Veuillez réessayer ultérieurement.';
        }
        return self::removeSourcesFromAnswer($response['response']);
    }

    public function llm2(Request $request)
    {
        // TODO : validate request
        $template = $request->string('template');
        $prompt = $request->string('prompt');
        $questionsAndAnswers = $request->input('q_and_a', []);
        $response = ApiUtils::generate_from_template($template, $prompt, $questionsAndAnswers);
        if (!isset($response['error']) || $response['error']) {
            return 'Une erreur s\'est produite. Veuillez réessayer ultérieurement.';
        }
        return self::removeSourcesFromAnswer($response['response']);
    }

    public function streamFile(string $secret, StreamOneFileRequest $request)
    {
        /** @var File $file */
        $file = File::where('secret', $secret)->where('is_deleted', false)->first();

        if (!$file) {
            return response()->json(['error' => 'Unknown file.'], 500);
        }

        /** @var \App\Models\Collection $collection */
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
        /** @var File $file */
        $file = File::where('secret', $secret)->where('is_deleted', false)->first();

        if (!$file) {
            return response()->json(['error' => 'Unknown file.'], 500);
        }

        /** @var \App\Models\Collection $collection */
        $collection = $file->collection()->where('is_deleted', false)->first();

        if (!$collection) {
            return response()->json(['error' => 'Unknown collection.'], 500);
        }

        $storage = Storage::disk('files-s3');
        $path = $this->storagePath($collection, $file);

        if (!$storage->exists($path)) {
            return response()->json(['error' => 'Unknown storage path.'], 500);
        }

        $page = $request->integer('page', -1);

        if ($file->isPdf() && $page > 0) {

            $rawFile = self::storageFileName($file);

            if (!file_exists("/tmp/{$rawFile}")) {
                file_put_contents("/tmp/{$rawFile}", $storage->get($path));
            }
            if (file_exists("/tmp/{$rawFile}")) {

                $extractedPage = $this->tmpFileName($file, $page);

                if (!file_exists("/tmp/{$extractedPage}")) {

                    $process = Process::fromShellCommandline("pdfseparate -f \"{$page}\" -l \"$page\" \"/tmp/{$rawFile}\" \"/tmp/{$extractedPage}\"");
                    $process->run();

                    if (!$process->isSuccessful()) {
                        return $storage->download($path, null, [
                            'pragma' => 'private',
                            'Cache-Control' => 'private, max-age=3600',
                        ]);
                    }
                }
                if (file_exists("/tmp/{$extractedPage}")) {
                    return response()->download("/tmp/{$extractedPage}", null, [
                        'pragma' => 'private',
                        'Cache-Control' => 'private, max-age=3600',
                    ]);
                }
            }
        }

        $rawFile = self::storageFileName($file);

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

    public function unloadFramework(int $id, Request $request)
    {
        /** @var YnhFramework $framework */
        $framework = YnhFramework::where('id', $id)->firstOrFail();
        if ($framework->collection()) {
            File::where('is_deleted', false)
                ->where('collection_id', $framework->collection()->id)
                ->where('name', trim(basename($framework->file, '.jsonl')))
                ->where('extension', 'jsonl')
                ->delete();
        }
        return response()->json([
            'success' => 'The framework has been unloaded and will be removed soon.',
        ]);
    }

    public function loadFramework(int $id, Request $request)
    {
        /** @var YnhFramework $framework */
        $framework = YnhFramework::where('id', $id)->firstOrFail();

        /** @var \App\Models\Collection $collection */
        $collection = \App\Models\Collection::where('name', $framework->collectionName())
            ->where('is_deleted', false)
            ->first();

        if (!$collection) {
            if (!IsValidCollectionName::test($framework->collectionName())) {
                return response()->json(['error' => 'Invalid collection name.'], 500);
            }
            $collection = \App\Models\Collection::create(['name' => $framework->collectionName()]);
        }

        $path = Str::replace('.jsonl', '.2.jsonl', $framework->path());
        $url = self::saveLocalFile($collection, $path);

        if ($url) {
            return response()->json([
                'success' => 'The framework has been loaded and will be processed soon.',
                'url' => $url,
            ]);
        }
        return response()->json(['error' => 'The framework could not be loaded.'], 500);
    }

    public function uploadOneFile(UploadOneFileRequest $request)
    {
        /** @var \App\Models\Collection $collection */
        $collection = \App\Models\Collection::where('name', $request->string('collection'))->where('is_deleted', false)->first();

        if (!$collection) {
            if (!IsValidCollectionName::test($request->string('collection'))) {
                return response()->json(['error' => 'Invalid collection name.'], 500);
            }
            $collection = \App\Models\Collection::create(['name' => $request->string('collection')]);
        }
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'Missing file content.'], 500);
        }

        $file = $request->file('file');
        $url = self::saveUploadedFile($collection, $file);

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
        /** @var \App\Models\Collection $collection */
        $collection = \App\Models\Collection::where('name', $request->string('collection'))->where('is_deleted', false)->first();

        if (!$collection) {
            if (!IsValidCollectionName::test($request->string('collection'))) {
                return response()->json(['error' => 'Invalid collection name.'], 500);
            }
            $collection = \App\Models\Collection::create(['name' => $request->string('collection')]);
        }

        $files = $request->allFiles();
        $successes = [];
        $errors = [];

        foreach ($files['files'] as $file) {
            $url = self::saveUploadedFile($collection, $file);
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

    public function deleteFile(int $id)
    {
        \App\Models\File::where('id', $id)->update(['is_deleted' => true]);
        return response()->json([
            'success' => __('The file will be deleted soon!'),
        ]);
    }

    public function deleteCollection(int $id)
    {
        \App\Models\Collection::where('id', $id)->update(['is_deleted' => true]);
        return response()->json([
            'success' => __('The collection will be deleted soon!'),
        ]);
    }

    public function saveCollection(int $id, Request $request)
    {
        $this->validate($request, [
            'priority' => 'required|integer|min:0',
        ]);
        $priority = $request->string('priority');
        \App\Models\Collection::where('id', $id)->update(['priority' => $priority]);
        return response()->json([
            'success' => __('The collection has been saved!'),
        ]);
    }

    public function deleteChunk(int $id)
    {
        Chunk::where('id', $id)->update(['is_deleted' => true]);
        return response()->json([
            'success' => __('The chunk will be deleted soon!'),
        ]);
    }

    public function saveChunk(int $id, Request $request)
    {
        $this->validate($request, [
            'text' => 'required|string|min:0|max:5000',
        ]);
        $text = $request->string('text');

        /** @var Chunk $chunk */
        $chunk = Chunk::find($id);
        $chunk->text = $text;
        $chunk->save();

        $response = ApiUtils::delete_chunks([$id], $chunk->collection->name);

        if ($response['error']) {

            Log::error($response['error_details']);

            return response()->json([
                'error' => __('The chunk has been saved but the embeddings could not be updated.'),
            ]);
        }

        $chunk->is_embedded = false;
        $chunk->save();

        $response = ApiUtils::import_chunks([[
            'uid' => (string)$chunk->id,
            'text' => $chunk->text,
            'tags' => $chunk->tags()->pluck('tag')->toArray(),
        ]], $chunk->collection->name);

        if ($response['error']) {

            Log::error($response['error_details']);

            return response()->json([
                'error' => __('The chunk has been saved but the embeddings could not be updated.'),
            ]);
        }

        $chunk->is_embedded = true;
        $chunk->save();

        return response()->json([
            'success' => __('The chunk has been saved!'),
        ]);
    }

    public function deleteConversation(int $id)
    {
        Conversation::where('id', $id)->delete();
        return response()->json([
            'success' => __('The conversation has been deleted!'),
        ]);
    }

    public function deletePrompt(int $id)
    {
        Prompt::where('id', $id)->delete();
        return response()->json([
            'success' => __('The prompt has been deleted!'),
        ]);
    }

    public function savePrompt(int $id, Request $request)
    {
        $this->validate($request, [
            'template' => 'required|string|min:0|max:5000',
        ]);
        $text = $request->string('template');
        Prompt::where('id', $id)->update(['template' => $text]);
        return response()->json([
            'success' => __('The prompt has been saved!'),
        ]);
    }

    public function listTables(Request $request)
    {
        $validated = $this->validate($request, [
            'storage' => ['required', Rule::enum(StorageType::class)],
            'region' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'access_key_id' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'secret_access_key' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'connection_string' => 'required_if:storage,' . StorageType::AZURE_BLOB_STORAGE->value . '|string|min:0|max:200',
            'input_folder' => 'required|string|min:0|max:100',
            'output_folder' => 'required|string|min:0|max:100',
        ]);
        try {
            $credentials = TableStorage::credentialsFromOptions($validated);
            $disk = TableStorage::inDisk($credentials);
            $diskFiles = $disk->files();
            $files = [];
            foreach ($diskFiles as $diskFile) {
                $extension = pathinfo($diskFile, PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), ['tsv'])) { // only TSV files are allowed
                    $files[] = [
                        'object' => $diskFile,
                        'size' => \Illuminate\Support\Number::format($disk->size($diskFile), locale: 'sv'),
                        'last_modified' => Carbon::createFromTimestamp($disk->lastModified($diskFile))->format('Y-m-d H:i') . ' UTC',
                    ];
                }
            }
            return response()->json([
                'success' => 'The tables have been listed.',
                'tables' => collect($files)->sortBy('object')->values()->all(),
            ]);
        } catch (UnableToListContents|UnableToRetrieveMetadata $e) {
            return response()->json(['error' => __('Unable to list files: ') . $e->getMessage(),]);
        }
    }

    public function listTablesColumns(Request $request)
    {
        $validated = $this->validate($request, [
            'storage' => ['required', Rule::enum(StorageType::class)],
            'region' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'access_key_id' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'secret_access_key' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'connection_string' => 'required_if:storage,' . StorageType::AZURE_BLOB_STORAGE->value . '|string|min:0|max:200',
            'input_folder' => 'string|min:0|max:100',
            'output_folder' => 'string|min:0|max:100',
            'tables' => 'required|array|min:1|max:1',
            'tables.*' => 'required|string|min:0|max:250',
        ]);
        $credentials = TableStorage::credentialsFromOptions($validated);

        $tables = collect($validated['tables']);
        $columns = $tables->map(function (string $table) use ($credentials) {

            $clickhouseTable = TableStorage::inClickhouseTableFunction($credentials, $table);

            return [
                'table' => $table,
                'columns' => ClickhouseLocal::describeTable($clickhouseTable),
            ];
        });
        return response()->json([
            'success' => 'The table columns have been listed.',
            'tables' => collect($columns)->sortBy('table')->values()->all(),
        ]);
    }

    public function importTables(Request $request)
    {
        $validated = $this->validate($request, [
            'storage' => ['required', Rule::enum(StorageType::class)],
            'region' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'access_key_id' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'secret_access_key' => 'required_if:storage,' . StorageType::AWS_S3->value . '|string|min:0|max:100',
            'connection_string' => 'required_if:storage,' . StorageType::AZURE_BLOB_STORAGE->value . '|string|min:0|max:200',
            'input_folder' => 'string|min:0|max:100',
            'output_folder' => 'string|min:0|max:100',
            'tables' => 'required|array|min:1|max:500',
            'tables.*.table' => 'required|string|min:1|max:100',
            'tables.*.old_name' => 'required|string|min:1|max:100',
            'tables.*.new_name' => 'required|string|min:1|max:100',
            'tables.*.type' => 'required|string|min:1|max:50',
            'updatable' => 'required|boolean',
            'copy' => 'required|boolean',
            'deduplicate' => 'required|boolean',
            'description' => 'required|string|min:1',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $count = TableStorage::dispatchImportTable($validated, $user);
        return response()->json([
            'success' => "{$count} table will be imported soon.",
        ]);
    }

    public function availableTables(Request $request)
    {
        return response()->json([
            'success' => 'The tables have been listed.',
            'tables' => Table::query()
                ->orderBy('name')
                ->get()
                ->map(fn(Table $table) => [
                    'name' => $table->name,
                    'nb_rows' => \Illuminate\Support\Number::format($table->nb_rows, locale: 'sv'),
                    'nb_columns' => count($table->schema),
                    'description' => $table->description,
                    'last_update' => $table->finished_at ? $table->finished_at->format('Y-m-d H:i') : '',
                    'status' => $table->status(),
                ]),
        ]);
    }

    public function queryTables(Request $request)
    {
        $this->validate($request, [
            'query' => 'required|string|min:1|max:5000',
            'store' => 'required|boolean',
        ]);

        $user = Auth::user();
        $name = $request->input('name', 'v_table');
        $description = $request->input('description', '');
        $query = $request->input('query');
        $store = $request->boolean('store', false);
        $materialize = $request->boolean('materialize', false);

        if ($store) {
            if ($materialize) {
                ImportVirtualTable::dispatch($user, $name, $query, $description);
                response()->json([
                    'success' => 'The table will be materialized soon.',
                    'result' => []
                ]);
            }

            $tableName = ClickhouseUtils::normalizeTableName($name);
            /** @var Table $tbl */
            $tbl = Table::updateOrCreate([
                'name' => $tableName,
                'created_by' => $user->id,
            ], [
                'name' => $tableName,
                'description' => $description,
                'copied' => $materialize,
                'deduplicated' => false,
                'last_error' => null,
                'started_at' => Carbon::now(),
                'finished_at' => null,
                'created_by' => $user->id,
                'query' => $query,
            ]);

            $output = ClickhouseClient::dropViewIfExists($tableName);

            if (!$output) {
                $tbl->last_error = 'Error #8';
                $tbl->save();
                return response()->json(['error' => 'The query cannot be stored.']);
            }

            $query = "CREATE VIEW {$tableName} AS {$query}";
            $output = ClickhouseClient::executeQuery($query);

            if (!$output) {
                $tbl->last_error = 'Error #9';
                $tbl->save();
                return response()->json(['error' => 'The query cannot be stored.']);
            }

            $tbl->last_error = null;
            $tbl->finished_at = Carbon::now();
            $tbl->schema = ClickhouseClient::describeTable($tableName);
            $tbl->nb_rows = ClickhouseClient::numberOfRows($tableName) ?? 0;
            $tbl->save();

            $query = "SELECT * FROM {$tableName} LIMIT 10 FORMAT TabSeparatedWithNames";
        } else {
            $query = "WITH t AS ({$query}) SELECT * FROM t LIMIT 10 FORMAT TabSeparatedWithNames";
        }

        $output = ClickhouseClient::executeQuery($query);

        if (!$output) {
            return response()->json([
                'error' => 'The query has failed.',
                'message' => ClickhouseClient::getExecuteQueryLastError(),
            ]);
        }
        return response()->json([
            'success' => 'The query has been executed.',
            'result' => collect(explode("\n", $output))
                ->filter(fn(string $line) => $line !== '')
                ->map(fn(string $line) => explode("\t", $line))
                ->values()
                ->all(),
        ]);
    }

    public function promptToTablesQuery(Request $request)
    {
        $this->validate($request, [
            'prompt' => 'required|string|min:1|max:5000',
        ]);

        $user = Auth::user();
        $prompt = $request->input('prompt');
        $query = ClickhouseUtils::promptToQuery(Table::where('created_by', $user->id)->get(), $prompt);

        if (empty($query)) {
            return response()->json(['error' => 'The query generation has failed.']);
        }
        return response()->json([
            'success' => 'The query generation has succeeded.',
            'result' => OpenAi::cleanSqlQuery($query),
        ]);
    }

    public function handle(): void
    {
        $botman = app('botman');

        $botman->hears('/stop', fn(BotMan $botman) => $botman->reply('Conversation stopped.'))->stopsConversation();

        $botman->hears('/login {username} {password}', function (BotMan $botman, string $username, string $password) {
            $user = $this->user($botman);
            if ($user) {
                $botman->reply('You are now logged in.');
            } else {
                $user = User::where('email', $username)->first();
                if (!$user) {
                    $botman->reply('Invalid username or password.');
                } else {
                    if (Auth::attempt(['email' => $username, 'password' => $password])) {
                        $botman->userStorage()->save(['user_id' => $user->id]);
                        $botman->reply('You are now logged in.');
                    } else {
                        $botman->reply('Invalid username or password.');
                    }
                }
            }
        })->skipsConversation();

        $botman->hears('/servers', function (BotMan $botman) {
            $user = $this->user($botman);
            $servers = $user ? YnhServer::forUser($user) : collect();
            if ($servers->isEmpty()) {
                $botman->reply('Connectez-vous pour accéder à cette commande.<br>Pour ce faire, vous pouvez utiliser la commande <b>/login {username} {password}</b>');
            } else {
                $list = $servers->filter(fn(YnhServer $server) => $server->ip())
                    ->map(function (YnhServer $server) use ($botman, $user) {
                        $json = base64_encode(json_encode($server));
                        $name = $server->name;
                        $ipv4 = $server->ip();
                        $ipv6 = $server->ipv6() ?: '-';
                        $domains = $server->isYunoHost() ? $server->domains->count() : '-';
                        $applications = $server->isYunoHost() ? $server->applications->count() : '-';
                        $users = $server->isYunoHost() ? $server->users->count() : '-';
                        $linkServer = $server->isYunoHost() ?
                            '<a href="' . route('ynh.servers.edit', $server->id) . '" target="_blank">' . $name . '</a>' :
                            '<a href="' . route('home', ['tab' => 'servers', 'servers_type' => 'instrumented']) . '" target="_blank">' . $name . '</a>';
                        $linkDomains = $domains === '-' ? $domains : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=domains\" target=\"_blank\">$domains</a>";
                        $linkApplications = $applications === '-' ? $applications : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=applications\" target=\"_blank\">$applications</a>";
                        $linkUsers = $users === '-' ? $users : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=users\" target=\"_blank\">$users</a>";
                        return "
                          <tr data-json=\"{$json}\">
                            <td>{$linkServer}</td>
                            <td>{$ipv4}</td>
                            <td>{$ipv6}</td>
                            <td>{$linkDomains}</td>
                            <td>{$linkApplications}</td>
                            <td>{$linkUsers}</td>
                          </tr>
                        ";
                    })
                    ->join("");
                $botman->reply("
                    <table data-type=\"table\" style=\"width:100%\">
                      <thead>
                          <tr>
                            <th>Name</th>
                            <th>IP V4</th>
                            <th>IP V6</th>
                            <th>Domains</th>
                            <th>Applications</th>
                            <th>Users</th>
                          </tr>
                      </thead>
                      <tbody>
                        {$list}
                      </tbody>
                    </table>
                ");
            }
        })->skipsConversation();

        $botman->hears('/question ([a-zA-Z0-9]+) (.*)', function (BotMan $botman, string $collection, string $question) {
            /** @var Prompt $prompt */
            $prompt = Prompt::where('name', 'default_debugger')->firstOrfail();
            $response = ApiUtils::ask_chunks_demo($collection, $question, $prompt->template);
            if ($response['error']) {
                $botman->reply('Une erreur s\'est produite. Veuillez réessayer ultérieurement.');
            } else {
                $answer = self::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));
                $botman->reply($answer);
            }
        })->skipsConversation();

        $botman->hears('/autosave ([a-z0-9]+) (.*)', function (BotMan $botman, string $threadId, string $dom) {
            $user = $this->user($botman);
            if ($user) {
                $dom = Str::after($botman->getMessage()->getPayload()['message'], "/autosave {$threadId} ");
                $conversation = Conversation::updateOrCreate([
                    'thread_id' => $threadId
                ], [
                    'thread_id' => $threadId,
                    'autosaved' => true,
                    'dom' => $dom,
                ]);
            }
        });

        $botman->hears('/rate ([a-z0-9]+) (.*)', function (BotMan $botman, string $threadId, string $dom) {
            $user = $this->user($botman);
            if ($user) {
                $dom = Str::after($botman->getMessage()->getPayload()['message'], "/rate {$threadId} ");
                $conversation = Conversation::updateOrCreate([
                    'thread_id' => $threadId
                ], [
                    'thread_id' => $threadId,
                    'autosaved' => false,
                    'dom' => $dom,
                ]);
            }
        });

        $botman->hears('{message}', function (BotMan $botman, string $message) {
            if (Str::startsWith($message, "/")) {
                return;
            }
            $user = $this->user($botman);
            if (!$user) {
                $botman->reply('Connectez-vous pour accéder à cette commande.<br>Pour ce faire, vous pouvez utiliser la commande <b>/login {username} {password}</b>');
            } else {
                $botman->startConversation(new QuestionsAndAnswers($message));
            }
        });

        $botman->fallback(fn(BotMan $botman) => $botman->reply('Désolé, je n\'ai pas compris votre commande.'));
        $botman->listen();
    }

    private function user(BotMan $botman): ?User
    {
        /** @var int $userId */
        $userId = $botman->userStorage()->get('user_id');
        if ($userId) {
            return User::find($userId);
        }
        /** @var User $user */
        $user = Auth::user();
        if ($user) {
            $botman->userStorage()->save(['user_id' => $user->id]);
        }
        return $user;
    }

    private function storagePath(\App\Models\Collection $collection, File $file): string
    {
        return self::storageFilePath($collection) . "/" . self::storageFileName($file);
    }

    private function tmpFileName(File $file, int $page): string
    {
        return "{$file->id}_{$page}_{$file->name_normalized}.{$file->extension}";
    }
}