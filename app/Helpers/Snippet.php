<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Based on @{link https://boyter.org/2013/04/building-a-search-result-extract-generator-in-php/}
 */
class Snippet
{
    private const UNWANTED_CHARACTERS = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
        'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');

    public static function normalize(string $string): string
    {
        return Str::lower(strtr($string, self::UNWANTED_CHARACTERS));
    }

    /**
     * 1/6 ratio on prevCount tends to work pretty well and puts the terms in the middle of the extract.
     *
     * @param array $words List of words around which to center the snippet.
     * @param string $text Text to snippet.
     * @return Collection The snippets.
     */
    public static function extract(array $words, string $text, bool $and = false): Collection
    {
        return self::extractEx($words, $text, 300, 50, '...', $and);
    }

    /**
     * Extract a snippet around the given words.
     *
     * @param array $words List of words around which to center the snippet.
     * @param string $text The text to snippet.
     * @param int $relLength Maximum length of the snippet in characters.
     * @param int $prevCount Number of characters to display before the leftmost match.
     * @param string|null $indicator Ellipsis to indicate where the text has been trimmed.
     * @return Collection The snippets.
     */
    public static function extractEx(array $words, string $text, int $relLength, int $prevCount, ?string $indicator = '...', bool $and = false): Collection
    {
        if (empty($words)) {
            throw new InvalidArgumentException("words array cannot be empty");
        }
        if ($relLength < 0 || $prevCount < 0) {
            throw new InvalidArgumentException("relLength and prevCount must be >= 0");
        }

        $indicator = $indicator ?? '...'; // for backward compatibility
        $textLength = Str::length($text);
        $locations = self::wordsLocations(array_unique($words), $text);

        if (count($locations) === 0) {
            return collect();
        }
        if ($and) {
            $matched = array_unique(array_map(fn(array $location) => $location['text'], $locations));
            if (count($matched) < count($words)) {
                return collect();
            }
        }
        if ($textLength <= $relLength) {
            return collect([$text]);
        }

        $startPos = self::snippetLocation($locations, $relLength, $prevCount);
        $begin = $startPos;
        $end = min($textLength, $startPos + $relLength);

        // If we are going to snip too much...
        if (($begin + $relLength) > $textLength) {
            $begin = max(0, $textLength - $relLength);
        }

        // Check to ensure we dont snip the first word
        if ($begin > 0) {
            $index = self::lastIndexOf($text, ' ', $begin);
            $begin = $index === false ? max(0, $begin - $prevCount) : $index + 1;
        }

        // Check to ensure we don't snip the last word
        if (($begin + $relLength) < $textLength) {
            $index = Str::position($text, ' ', $end);
            $end = $index === false ? min($textLength, $end + $prevCount) : $index;
        }

        // If we trimmed from the begin/end add ellipsis
        return collect([($begin > 0 ? $indicator : "") . Str::substr($text, $begin, $end - $begin) . ($end < $textLength ? $indicator : "")]);
    }

    /**
     * Find the locations of each word.
     *
     * @param array $words Words to search for.
     * @param string $text The text to search.
     * @return array List of word spans (start, end).
     */
    public static function wordsLocations(array $words, string $text): array
    {
        $locations = [];

        foreach ($words as $word) {

            $length = Str::length($word);
            $loc = Str::position($text, $word);

            while ($loc !== false) {

                $precedingChar = $loc === 0 ? null : Str::charAt($text, $loc - 1);

                if ($loc === 0 || !ctype_alnum($precedingChar)) {
                    $locations[] = ['begin' => $loc, 'text' => $word, 'end' => $loc + $length];
                }

                $loc = Str::position($text, $word, $loc + $length);
            }
        }
        usort($locations, function ($a, $b) {
            return $a['begin'] <=> $b['begin'];
        });
        return $locations;
    }

    /**
     * Determine the starting point for the most relevant snippet.
     *
     * @param array $locations List of word locations.
     * @param int $relLength Maximum length of the snippet in characters.
     * @param int $prevCount Number of characters before the leftmost match.
     * @return int Start position of the snippet.
     */
    private static function snippetLocation(array $locations, int $relLength, int $prevCount): int
    {
        if (empty($locations)) {
            throw new InvalidArgumentException("locations cannot be empty");
        }

        $bestLocation = 0;
        $bestDiff = 0;
        $nbDistinctWords = 0;

        for ($i = 0; $i < count($locations); $i++) {

            $endPos = $locations[$i]['begin'] + Str::length($locations[$i]['text']);
            $beginPos = $locations[$i]['begin'];
            $words = [$locations[$i]['text']];

            for ($j = $i + 1; $j < count($locations) && ($locations[$j]['begin'] - $beginPos) < $relLength; $j++) {
                $endPos = $locations[$j]['begin'] + Str::length($locations[$j]['text']);
                $words[] = $locations[$j]['text'];
            }

            $distinctWordCount = count(array_unique($words));

            if ($distinctWordCount > $nbDistinctWords /* maximize the number of distinct words */ ||
                ($distinctWordCount === $nbDistinctWords && $endPos - $beginPos < $bestDiff) /* minimize the window size */) {
                $bestLocation = $beginPos;
                $bestDiff = $endPos - $beginPos;
                $nbDistinctWords = $distinctWordCount;
            }
        }
        return max(0, $bestLocation - $prevCount);
    }

    /**
     * Finds the last index of a given substring in a string, starting from a given offset.
     *
     * @param string $text The string to search in.
     * @param string $needle The substring to search for.
     * @param int|null $offset The position (exclusive) from which to search backward. Optional.
     * @return int|bool The position of the last occurrence of the substring, or false if not found.
     */
    private static function lastIndexOf(string $text, string $needle, ?int $offset = null)
    {
        // If no offset is given, start from the end of the string
        $offset = $offset ?? Str::length($text);

        // Extract substring up to the given offset
        $substring = Str::substr($text, 0, $offset);

        // Find the last occurrence of the needle in the substring
        $lastPosition = Str::length(Str::beforeLast($substring, $needle));

        return $lastPosition === Str::length($substring) ? false : $lastPosition; // false if not found
    }
}
