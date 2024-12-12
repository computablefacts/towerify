<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class SimpleAdditiveWeighting
{
    // $criteria = [
    //      "criterion" => [
    //          "type" => <"benefit"|"cost">,
    //          "weight" => <float>,
    //          "transform" => function(string $criterion, $item): string {...},
    //      ],
    //      ...
    // ]
    private array $criteria;

    const string BENEFIT = "benefit";
    const string COST = "cost";
    const string VERY_LOW = "very low";
    const string LOW = "low";
    const string MODERATE = "moderate";
    const string HIGH = "high";
    const string VERY_HIGH = "very high";

    public function __construct(array $criteria)
    {
        $this->criteria = $criteria;
        $total = collect($criteria)->values()->map(fn(array $item) => $item['weight'])->sum();
        if ($total != 100) {
            throw new \Exception("The sum of all weights must be equal to 100, got {$total}");
        }
        $positive = collect($criteria)->values()->map(fn(array $item) => $item['weight'])->doesntContain(fn(int $value) => $value < 0);
        if (!$positive) {
            throw new \Exception("All weights must be positive");
        }
    }

    public function scoreAll(Collection $items): Collection
    {
        return $this
            ->normalizeAll($items)
            ->map(function (array $item) {
                $score = 0;
                foreach ($this->criteria as $criterion => $params) {
                    $score += $item["new"][$criterion] * $this->weight($criterion);
                }
                return [
                    'item' => $item["old"],
                    'score' => $score,
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    private function normalizeAll(Collection $items): Collection
    {
        $minsAndMaxes = $this->getMinAndMaxForEachCriterion($items);
        return $this
            ->transformAll($items)
            ->map(function (array $item) use ($minsAndMaxes) {
                $newItem = [];
                foreach ($this->criteria as $criterion => $params) {
                    if ($this->isCost($criterion)) {
                        $newItem[$criterion] = $minsAndMaxes[$criterion][0] / $item["new"][$criterion];
                    } else if ($this->isBenefit($criterion)) {
                        $newItem[$criterion] = $item["new"][$criterion] / $minsAndMaxes[$criterion][1];
                    } else {
                        throw new \Exception("missing criterion '{$criterion}' in " . json_encode($item));
                    }
                }
                return [
                    "old" => $item["old"],
                    "new" => $newItem
                ];
            });
    }

    private function transformAll(Collection $items): Collection
    {
        return $items
            ->map(function (array $oldItem) {
                $newItem = [];
                foreach ($this->criteria as $criterion => $params) {
                    $newItem[$criterion] = $this->transform($criterion, $oldItem);
                }
                return [
                    "old" => $oldItem,
                    "new" => $newItem
                ];
            });
    }

    private function getMinAndMaxForEachCriterion(Collection $items): array
    {
        return $this
            ->transformAll($items)
            ->map(fn(array $items) => $items["new"])
            ->map(function (array $items) {
                $minsAndMaxes = [];
                foreach ($this->criteria as $criterion => $params) {
                    $minsAndMaxes[$criterion] = [
                        $items[$criterion] == 0 ? PHP_FLOAT_MAX : $items[$criterion],
                        $items[$criterion] == 0 ? PHP_FLOAT_MIN : $items[$criterion]
                    ];
                }
                return $minsAndMaxes;
            })
            ->reduce(function (?array $carry, array $items) {
                if (is_null($carry)) {
                    return $items;
                }
                $minsAndMaxes = [];
                foreach ($items as $criterion => $minAndMax) {
                    $minsAndMaxes[$criterion] = [
                        min($carry[$criterion][0], $minAndMax[0]),
                        max($carry[$criterion][1], $minAndMax[1])
                    ];
                }
                return $minsAndMaxes;
            });
    }

    private function isBenefit(string $criterion): bool
    {
        return $this->criteria[$criterion]['type'] === self::BENEFIT;
    }

    private function isCost(string $criterion): bool
    {
        return $this->criteria[$criterion]['type'] === self::COST;
    }

    private function weight(string $criterion): float
    {
        return $this->criteria[$criterion]['weight'];
    }

    private function transform(string $criterion, $item): float
    {
        /** @var string $weight */
        $weight = $this->criteria[$criterion]['transform']($criterion, $item);
        return match ($weight) {
            self::VERY_LOW => 1,
            self::LOW => 2,
            self::MODERATE => 4,
            self::HIGH => 8,
            self::VERY_HIGH => 16,
            default => 0,
        };
    }
}