<?php
/**
 * Predicament triage scoring helper.
 * Scores a predicament based on keywords, farm context, and text length to surface urgent items first.
 */

if (!function_exists('score_predicament_priority')) {
    /**
     * @param array $predicament ['title' => string, 'description' => string]
     * @param array $farmContext ['farm_area' => string|float|null, 'farm_unit' => string|null, 'farm_type' => string|null]
     * @return array ['score' => int, 'reasons' => string[]]
     */
    function score_predicament_priority(array $predicament, array $farmContext = []): array
    {
        $title = strtolower($predicament['title'] ?? '');
        $description = strtolower($predicament['description'] ?? '');
        $text = $title . ' ' . $description;

        $score = 50; // base neutral score
        $reasons = [];

        $weights = [
            'high' => [
                'pest', 'disease', 'blight', 'fungus', 'wilt', 'infestation', 'locust', 'weevil',
                'dieback', 'rot', 'mold', 'mildew', 'virus', 'bacteria', 'drought', 'flood',
                'water scarcity', 'irrigation failure', 'crop failure'
            ],
            'medium' => [
                'nutrient', 'fertility', 'soil', 'ph', 'compaction', 'erosion', 'weed',
                'yield drop', 'low yield', 'market price', 'storage'
            ],
            'low' => [
                'record', 'documentation', 'general inquiry', 'info', 'advice', 'training'
            ],
        ];

        foreach ($weights['high'] as $kw) {
            if (strpos($text, $kw) !== false) {
                $score += 10;
                $reasons[] = "High-severity keyword: {$kw}";
            }
        }
        foreach ($weights['medium'] as $kw) {
            if (strpos($text, $kw) !== false) {
                $score += 5;
                $reasons[] = "Medium-severity keyword: {$kw}";
            }
        }
        foreach ($weights['low'] as $kw) {
            if (strpos($text, $kw) !== false) {
                $score -= 5;
                $reasons[] = "Low-severity keyword: {$kw}";
            }
        }

        // Farm scale heuristic: larger areas get higher priority.
        $area = isset($farmContext['farm_area']) ? floatval($farmContext['farm_area']) : 0;
        $unit = isset($farmContext['farm_unit']) ? $farmContext['farm_unit'] : '';
        if ($area > 0) {
            if ($area >= 20) {
                $score += 8;
                $reasons[] = "Large farm area ({$area} {$unit})";
            } elseif ($area >= 5) {
                $score += 4;
                $reasons[] = "Medium farm area ({$area} {$unit})";
            } else {
                $score += 1;
                $reasons[] = "Small farm area ({$area} {$unit})";
            }
        }

        // Farm type hints.
        $farmType = strtolower(isset($farmContext['farm_type']) ? $farmContext['farm_type'] : '');
        if ($farmType) {
            if (strpos($farmType, 'dairy') !== false || strpos($farmType, 'livestock') !== false) {
                $score += 3;
                $reasons[] = 'Livestock/dairy sensitivity';
            } elseif (strpos($farmType, 'vegetable') !== false || strpos($farmType, 'horticulture') !== false) {
                $score += 2;
                $reasons[] = 'Perishable crop sensitivity';
            }
        }

        // Longer descriptions usually mean richer detail; cap the impact.
        $descLen = strlen($description);
        if ($descLen >= 400) {
            $score += 3;
            $reasons[] = 'Detailed description provided';
        } elseif ($descLen < 60) {
            $score -= 2;
            $reasons[] = 'Very short description';
        }

        // Normalize score to 0-100.
        $score = max(0, min(100, intval(round($score))));

        return ['score' => $score, 'reasons' => $reasons];
    }
}
