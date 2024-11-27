<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\Modules\CyberBuddy\Conversations\QuestionsAndAnswers;
use App\Modules\CyberBuddy\Events\IngestFile;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Http\Requests\DownloadOneFileRequest;
use App\Modules\CyberBuddy\Http\Requests\StreamOneFileRequest;
use App\Modules\CyberBuddy\Http\Requests\UploadManyFilesRequest;
use App\Modules\CyberBuddy\Http\Requests\UploadOneFileRequest;
use App\Modules\CyberBuddy\Models\Chunk;
use App\Modules\CyberBuddy\Models\Conversation;
use App\Modules\CyberBuddy\Models\File;
use App\Modules\CyberBuddy\Models\Prompt;
use App\Modules\CyberBuddy\Models\Template;
use App\Modules\CyberBuddy\Rules\IsValidCollectionName;
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
use Symfony\Component\Process\Process;

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
        /** @var array $refs */
        $refs = $matches[0];
        foreach ($refs as $ref) {
            $id = Str::replace(['[', ']'], '', $ref);
            $tooltip = $sources->filter(fn($ctx) => $ctx['id'] == $id)->first();
            if ($tooltip) {
                $answer = Str::replace($ref, "
                  <div class=\"cb-tooltip\">
                    <b style=\"color:#f8b500\">[{$id}]</b>
                    <span class=\"cb-tooltiptext cb-tooltip-top\">{$tooltip['text']}</span>
                  </div>
                ", $answer);
            }
        }
        return Str::replace(["\n\n", "\n-"], "<br>", $answer);
    }

    public static function removeSourcesFromAnswer(string $answer): string
    {
        // Remove sources such as [[12]] or [[12],[13]] from the answer
        return preg_replace("/\[((\[\d+],?)+)]/", "", $answer);
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
        $name = $request->string('name');
        $template = $request->input('template', []);

        if (isset($template) && count($template) > 0) {
            if ($id === 0) {
                return Template::create([
                    'name' => (empty($name) ? "doc" : $name) . '-' . Carbon::now()->toIso8601ZuluString(),
                    'template' => $template,
                    'readonly' => false,
                ]);
            }
            return Template::updateOrCreate([
                'id' => $id,
                'created_by' => Auth::user()->id,
                'readonly' => false,
            ], [
                'template' => $template,
            ]);
        }
        return [];
    }

    public function collections()
    {
        return \App\Modules\CyberBuddy\Models\Collection::orderBy('name', 'asc')
            ->get()
            ->map(function (\App\Modules\CyberBuddy\Models\Collection $collection) {
                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
                ];
            });
    }

    public function llm1(Request $request)
    {
        // TODO : validate request
        /** @var Prompt $prompt */
        $prompt = Prompt::where('name', 'default_debugger')->firstOrfail();
        $response = ApiUtils::ask_chunks_demo($request->string('collection'), $request->string('instructions'), $prompt->template);
        if ($response['error']) {
            return 'Une erreur s\'est produite. Veuillez réessayer ultérieurement.';
        }
        return self::removeSourcesFromAnswer($response['response']);
    }

    public function llm2(Request $request)
    {
        // TODO : validate request
        $response = ApiUtils::ask_chunks_demo($request->string('collection'), $request->string('instructions'), $request->string('prompt'));
        if ($response['error']) {
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

        /** @var \App\Modules\CyberBuddy\Models\Collection $collection */
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

        /** @var \App\Modules\CyberBuddy\Models\Collection $collection */
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

            $rawFile = $this->storageFileName($file);

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
        /** @var \App\Modules\CyberBuddy\Models\Collection $collection */
        $collection = \App\Modules\CyberBuddy\Models\Collection::where('name', $request->string('collection'))->where('is_deleted', false)->first();

        if (!$collection) {
            if (!IsValidCollectionName::test($request->string('collection'))) {
                return response()->json(['error' => 'Invalid collection name.'], 500);
            }
            $collection = \App\Modules\CyberBuddy\Models\Collection::create(['name' => $request->string('collection')]);
        }
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'Missing file content.'], 500);
        }

        $file = $request->file('file');
        $url = $this->saveOneFile($collection, $file);

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
        /** @var \App\Modules\CyberBuddy\Models\Collection $collection */
        $collection = \App\Modules\CyberBuddy\Models\Collection::where('name', $request->string('collection'))->where('is_deleted', false)->first();

        if (!$collection) {
            if (!IsValidCollectionName::test($request->string('collection'))) {
                return response()->json(['error' => 'Invalid collection name.'], 500);
            }
            $collection = \App\Modules\CyberBuddy\Models\Collection::create(['name' => $request->string('collection')]);
        }

        $files = $request->allFiles();
        $successes = [];
        $errors = [];

        foreach ($files['files'] as $file) {
            $url = $this->saveOneFile($collection, $file);
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
        \App\Modules\CyberBuddy\Models\File::where('id', $id)->update(['is_deleted' => true]);
        return response()->json([
            'success' => __('The file will be deleted soon!'),
        ]);
    }

    public function deleteCollection(int $id)
    {
        \App\Modules\CyberBuddy\Models\Collection::where('id', $id)->update(['is_deleted' => true]);
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
        \App\Modules\CyberBuddy\Models\Collection::where('id', $id)->update(['priority' => $priority]);
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
        Prompt::where('id', $id)->update(['is_deleted' => true]);
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

        $botman->hears('/rate ([a-z0-9]+) (.*)', function (BotMan $botman, string $threadId, string $dom) {
            $user = $this->user($botman);
            if ($user) {
                $dom = Str::after($botman->getMessage()->getPayload()['message'], "/rate {$threadId} ");
                $conversation = Conversation::updateOrCreate([
                    'thread_id' => $threadId
                ], [
                    'thread_id' => $threadId,
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

    private function saveOneFile(\App\Modules\CyberBuddy\Models\Collection $collection, UploadedFile $file): ?string
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
        IngestFile::dispatch(Auth::user(), $collection->name, $fileRef->id);

        return $fileRef->downloadUrl();
    }

    private function storagePath(\App\Modules\CyberBuddy\Models\Collection $collection, File $file): string
    {
        return "{$this->storageFilePath($collection)}/{$this->storageFileName($file)}";
    }

    private function storageFilePath(\App\Modules\CyberBuddy\Models\Collection $collection): string
    {
        return "/cyber-buddy/{$collection->id}";
    }

    private function storageFileName(File $file): string
    {
        return "{$file->id}_{$file->name_normalized}.{$file->extension}";
    }

    private function tmpFileName(File $file, int $page): string
    {
        return "{$file->id}_{$page}_{$file->name_normalized}.{$file->extension}";
    }
}