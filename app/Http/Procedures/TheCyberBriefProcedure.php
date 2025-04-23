<?php

namespace App\Http\Procedures;

use App\Helpers\OpenAi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sajya\Server\Attributes\RpcMethod;
use Sajya\Server\Procedure;

class TheCyberBriefProcedure extends Procedure
{
    public static string $name = 'the-cyber-brief';

    #[RpcMethod(
        description: "Summarize a text or a webpage.",
        params: [
            "url_or_text" => "The text or webpage (URL) to summarize. The webpage will be automatically downloaded and converted to text.",
            "prompt" => "The prompt to use. Any [TEXT] in the prompt will be replaced by the text or webpage.",
        ],
        result: [
            "summary" => "The summary of the text or webpage.",
        ]
    )]
    public function summarize(Request $request): array
    {
        $params = $request->validate([
            'url_or_text' => 'required|string',
            'prompt' => 'required|string',
        ]);

        $text = $request->string('url_or_text', '');
        $prompt = $request->string('prompt', '');
        $model = $request->string('model', 'gpt-4o');
        $temperature = $request->float('temperature', 0.7);
        $content = OpenAi::download($text);
        $response = OpenAi::execute(Str::replace('[TEXT]', $content, $prompt), $model, $temperature);

        if (isset($response['choices'][0]['message']['content'])) {
            return [
                "summary" => $response['choices'][0]['message']['content'],
            ];
        }
        throw new \Exception('An error occurred while summarizing the text or webpage.');
    }
}