<?php

namespace App\Providers;

use Baril\Sqlout\Engine as Engine;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use StopWords\StopWords;
use Wamania\Snowball\StemmerFactory;

class SqloutEngine extends Engine
{
    /**
     * Apply the filters to the indexed content or search terms, tokenize it
     * and stem the words.
     *
     * @param string $content
     * @return string
     */
    protected function processString($content)
    {
        if (Str::startsWith($content, ':')) {
            return parent::processString(Str::substr($content, 1));
        }

        $lang = Str::substr($content, 0, 2);
        $contentOriginal = Str::substr($content, 3);
        $content = $contentOriginal;

        // Apply custom filters:
        foreach (config('scout.sqlout.filters', []) as $filter) {
            if (is_callable($filter)) {
                $content = call_user_func($filter, $content);
            }
        }

        // Remove stopwords:
        try {
            $stopwords = new StopWords($lang);
            $content = $stopwords->clean($content);
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            return parent::processString($contentOriginal);
        }

        // Tokenize:
        $words = preg_split(config('scout.sqlout.token_delimiter', '/[\s]+/'), $content);

        // Remove short words:
        $minLength = config('scout.sqlout.minimum_length', 0);
        $words = collect($words)->reject(fn($word) => mb_strlen($word) < $minLength)->all();

        // Stem:
        try {
            $stemmer = StemmerFactory::create($lang);
            foreach ($words as $k => $word) {
                $words[$k] = $stemmer->stem($word);
            }
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            return parent::processString($contentOriginal);
        }

        // Return result:
        return implode(' ', $words);
    }
}
