<?php

namespace App\Helpers;

use InvalidArgumentException;

class Snippet
{
    /**
     * Extract a snippet with default parameters.
     *
     * @param array $words List of words around which to center the snippet.
     * @param string $text Text to snippet.
     * @return string The snippet.
     */
    public static function extract(array $words, string $text): string
    {
        return self::extractEx($words, $text, 300, 50, '...');
    }

    /**
     * Extract a snippet around the given words.
     *
     * @param array $words List of words around which to center the snippet.
     * @param string $text The text to snippet.
     * @param int $relLength Maximum length of the snippet in characters.
     * @param int $prevCount Number of characters to display before the leftmost match.
     * @param string|null $indicator Ellipsis to indicate where the text has been trimmed.
     * @return string The snippet.
     */
    public static function extractEx(array $words, string $text, int $relLength, int $prevCount, ?string $indicator = '...'): string
    {
        if (empty($words)) {
            throw new InvalidArgumentException("words array cannot be empty");
        }
        if ($relLength < 0 || $prevCount < 0) {
            throw new InvalidArgumentException("relLength and prevCount must be >= 0");
        }

        $indicator = $indicator ?? '...';
        $textLength = strlen($text);

        if ($textLength <= $relLength) {
            return $text;
        }

        $locations = self::wordsLocations(array_unique($words), $text);

        $startPos = !empty($locations) ? self::snippetLocation($locations, $relLength, $prevCount) : 0;
        $begin = $startPos;
        $end = min($textLength, $startPos + $relLength);

        if (($begin + $relLength) > $textLength) {
            $begin = max(0, $textLength - $relLength);
        }
        if ($begin > 0) {
            $index = strrpos(substr($text, 0, $begin), ' ');
            $begin = $index === false ? max(0, $begin - $prevCount) : $index + 1;
        }
        if (($begin + $relLength) < $textLength) {
            $index = strpos($text, ' ', $end);
            $end = $index === false ? min($textLength, $end + $prevCount) : $index;
        }
        return ($begin > 0 ? $indicator : "") . substr($text, $begin, $end - $begin) . ($end < $textLength ? $indicator : "");
    }

    /**
     * Find the locations of each word.
     *
     * @param array $words Words to search for.
     * @param string $text The text to search.
     * @return array List of word spans (start, end).
     */
    private static function wordsLocations(array $words, string $text): array
    {
        $locations = [];

        foreach ($words as $word) {
            $length = strlen($word);
            $loc = strpos($text, $word);

            while ($loc !== false) {
                $precedingChar = $loc === 0 ? null : $text[$loc - 1];

                if ($loc === 0 || !ctype_alnum($precedingChar)) {
                    $locations[] = ['begin' => $loc, 'text' => $word, 'end' => $loc + $length];
                }

                $loc = strpos($text, $word, $loc + $length);
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

            $endPos = $locations[$i]['begin'] + strlen($locations[$i]['text']);
            $beginPos = $locations[$i]['begin'];
            $words = [$locations[$i]['text']];

            for ($j = $i + 1; $j < count($locations) && ($locations[$j]['begin'] - $beginPos) < $relLength; $j++) {
                $endPos = $locations[$j]['begin'] + strlen($locations[$j]['text']);
                $words[] = $locations[$j]['text'];
            }

            $distinctWordCount = count(array_unique($words));

            if ($distinctWordCount > $nbDistinctWords || ($distinctWordCount == $nbDistinctWords && $endPos - $beginPos < $bestDiff)) {
                $bestLocation = $beginPos;
                $bestDiff = $endPos - $beginPos;
                $nbDistinctWords = $distinctWordCount;
            }
        }
        return max(0, $bestLocation - $prevCount);
    }
}
