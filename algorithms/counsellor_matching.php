<?php
/**
 * Counsellor recommendation helper.
 * Scores counsellors for a given predicament using location proximity, workload, and simple text affinity.
 */

if (!function_exists('rank_counsellors_for_predicament')) {
    /**
     * Rank counsellors for a predicament.
     *
     * @param array $counsellors Array of counsellor rows (expects id, name, address, email).
     * @param array $predicament Array with title, description, farmer_address (optional).
     * @param array $stats ['workload' => [counsellor_id => openCount]]
     * @return array Sorted counsellors with added score/reasons.
     */
    function rank_counsellors_for_predicament(array $counsellors, array $predicament, array $stats = []): array
    {
        $workload = $stats['workload'] ?? [];
        $scored = [];
        foreach ($counsellors as $counsellor) {
            $scoreData = score_counsellor_match($counsellor, $predicament, $workload);
            $counsellor['match_score'] = $scoreData['score'];
            $counsellor['match_reasons'] = $scoreData['reasons'];
            $scored[] = $counsellor;
        }
        usort($scored, function ($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });
        return $scored;
    }

    /**
     * Score a single counsellor for a predicament.
     *
     * @param array $counsellor
     * @param array $predicament expects keys: title, description, farmer_address
     * @param array $workloadMap map of counsellor_id => open guideline count
     * @return array ['score' => int, 'reasons' => string[]]
     */
    function score_counsellor_match(array $counsellor, array $predicament, array $workloadMap = []): array
    {
        $score = 50;
        $reasons = [];

        $farmerAddress = strtolower($predicament['farmer_address'] ?? '');
        $counsellorAddress = strtolower($counsellor['address'] ?? '');

        // Location proximity: shared tokens in address.
        if ($farmerAddress && $counsellorAddress) {
            $farmerParts = array_filter(explode(' ', preg_replace('/[^a-z0-9 ]/', ' ', $farmerAddress)));
            $matches = 0;
            foreach ($farmerParts as $part) {
                if (strlen($part) < 3) {
                    continue;
                }
                if (strpos($counsellorAddress, $part) !== false) {
                    $matches++;
                }
            }
            if ($matches >= 2) {
                $score += 12;
                $reasons[] = 'Same locality/district';
            } elseif ($matches === 1) {
                $score += 6;
                $reasons[] = 'Nearby location';
            } else {
                $score -= 2;
                $reasons[] = 'Different location';
            }
        }

        // Text affinity: simple overlap between predicament title and counsellor name/email (placeholder for expertise tags).
        $title = strtolower($predicament['title'] ?? '');
        $desc = strtolower($predicament['description'] ?? '');
        $text = $title . ' ' . $desc;
        $nameEmail = strtolower(($counsellor['name'] ?? '') . ' ' . ($counsellor['email'] ?? ''));
        $keywords = ['soil', 'pest', 'market', 'irrigation', 'seed', 'crop', 'disease', 'water'];
        foreach ($keywords as $kw) {
            if (strpos($text, $kw) !== false && strpos($nameEmail, $kw) !== false) {
                $score += 5;
                $reasons[] = "Matches expertise hint: {$kw}";
            }
        }

        // Workload balance: fewer open guidelines = higher score.
        $open = $workloadMap[$counsellor['id']] ?? 0;
        if ($open <= 2) {
            $score += 8;
            $reasons[] = 'Low workload';
        } elseif ($open <= 5) {
            $score += 3;
            $reasons[] = 'Moderate workload';
        } else {
            $score -= 5;
            $reasons[] = 'High workload';
        }

        $score = max(0, min(100, intval(round($score))));
        return ['score' => $score, 'reasons' => $reasons];
    }
}
