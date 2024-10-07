<?php

namespace App\Modules\CyberBuddy\Http\Controllers;

use App\Models\YnhServer;
use App\Modules\AdversaryMeter\Http\Controllers\Controller;
use App\Modules\CyberBuddy\Events\IngestFile;
use App\Modules\CyberBuddy\Helpers\ApiUtilsFacade as ApiUtils;
use App\Modules\CyberBuddy\Http\Conversations\FrameworksConversation;
use App\Modules\CyberBuddy\Http\Requests\DownloadOneFileRequest;
use App\Modules\CyberBuddy\Http\Requests\StreamOneFileRequest;
use App\Modules\CyberBuddy\Http\Requests\UploadManyFilesRequest;
use App\Modules\CyberBuddy\Http\Requests\UploadOneFileRequest;
use App\Modules\CyberBuddy\Models\File;
use App\Modules\CyberBuddy\Rules\IsValidCollectionName;
use App\User;
use BotMan\BotMan\BotMan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CyberBuddyController extends Controller
{
    public static function enhanceAnswerWithSources(string $answer, Collection $sources): string
    {
        $matches = [];
        $isOk = preg_match_all("/\[\[\d+]]/", $answer, $matches);
        if (!$isOk) {
            return Str::replace(["\n\n", "\n-"], "<br>", $answer);
        }
        /** @var array $refs */
        $refs = $matches[0];
        foreach ($refs as $ref) {
            $id = Str::replace(['[', ']'], '', $ref);
            $tooltip = $sources->filter(fn($ctx) => $ctx['id'] === $id)->first();
            if ($tooltip) {
                $answer = Str::replace($ref, "
                  <div class=\"tooltip\">
                    <b style=\"color:#f8b500\">[{$id}]</b>
                    <span class=\"tooltiptext tooltip-top\">{$tooltip['text']}</span>
                  </div>
                ", $answer);
            }
        }
        return Str::replace(["\n\n", "\n-"], "<br>", $answer);
    }

    public function __construct()
    {
        //
    }

    public function showPage()
    {
        return view('modules.cyber-buddy.page');
    }

    public function showChat()
    {
        return view('modules.cyber-buddy.chat');
    }

    public function files()
    {
        return File::select('cb_files.*')
            ->join('cb_collections', 'cb_collections.id', '=', 'cb_files.collection_id')
            ->where('cb_files.is_deleted', false)
            ->where('cb_collections.is_deleted', false)
            ->orderBy('cb_collections.name')
            ->orderBy('name_normalized')
            ->get()
            ->map(function (File $file) {
                $nbChunks = $file->chunks()->count();
                $nbVectors = $file->chunks()->where('is_embedded', true)->count();
                $nbNotVectors = $file->chunks()->where('is_embedded', false)->count();
                return [
                    'collection' => $file->collection->name,
                    'filename' => "{$file->name_normalized}.{$file->extension}",
                    'size' => $file->size,
                    'nb_chunks' => $nbChunks,
                    'nb_vectors' => $nbVectors,
                    'nb_not_vectors' => $nbNotVectors,
                    'status' => $nbVectors != 0 && $nbNotVectors === 0 ? 'processed' : 'processing',
                    'download_url' => $file->downloadUrl(),
                ];
            });
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

        $files = $request->file('files');
        $successes = [];
        $errors = [];

        foreach ($files as $file) {
            $url = $this->saveOneFile($collection, $file);
            if ($url) {
                $successes[] = $url;
            } else {
                $errors[] = $file->getClientOriginalName();
            }
        }
        if (count($errors) > 0) {
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

    public function handle(): void
    {
        $botman = app('botman');
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
                        $os = isset($os_infos[$server->id]) && $os_infos[$server->id]->count() >= 1 ? $os_infos[$server->id][0]->os : '-';
                        $ipv4 = $server->ip();
                        $ipv6 = $server->isFrozen() || $server->ipv6() === '<unavailable>' ? '-' : $server->ipv6();
                        $domains = $server->isFrozen() || $server->addedWithCurl() ? '-' : $server->domains->count();
                        $applications = $server->isFrozen() || $server->addedWithCurl() ? '-' : $server->applications->count();
                        $users = $server->isFrozen() || $server->addedWithCurl() ? '-' : $server->users->count();
                        $linkServer = '<a href="' . route('ynh.servers.edit', $server->id) . '" target="_blank">' . $name . '</a>';
                        $linkDomains = $domains === '-' ? $domains : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=domains\" target=\"_blank\">$domains</a>";
                        $linkApplications = $applications === '-' ? $applications : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=applications\" target=\"_blank\">$applications</a>";
                        $linkUsers = $users === '-' ? $users : '<a href="' . route('ynh.servers.edit', $server->id) . "?tab=users\" target=\"_blank\">$users</a>";
                        return "
                          <tr data-json=\"{$json}\">
                            <td>{$linkServer}</td>
                            <td>{$os}</td>
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
                            <th>OS</th>
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
        $botman->hears('/question {question}', function (BotMan $botman, string $question) {
            $botman->types();
            $response = ApiUtils::ask_chunks_demo($question);
            if ($response['error']) {
                $botman->reply('Une erreur s\'est produite. Veuillez réessayer ultérieurement.');
            } else {
                $answer = self::enhanceAnswerWithSources($response['response'], collect($response['context'] ?? []));
                $botman->reply($answer);
            }
        })->skipsConversation();
        $botman->hears('/frameworks', fn(BotMan $botman) => $botman->startConversation(new FrameworksConversation()));
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
        event(new IngestFile(Auth::user(), $collection->name, $fileRef->id));

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
}