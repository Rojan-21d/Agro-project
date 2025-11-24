<?php
/**
 * Guideline similarity helper.
 * Uses simple TF-based cosine similarity on title/description to surface related guidelines.
 */

if (!function_exists('rank_similar_guidelines')) {
    /**
     * @param array $target ['title' => string, 'description' => string]
     * @param array $candidates array of guidelines (expects gid/title/description and optionally predicament_title)
     * @param int $limit number of top results to return
     * @return array candidates with added similarity score (0-100)
     */
    function rank_similar_guidelines(array $target, array $candidates, int $limit = 5): array
    {
        $targetVec = build_term_vector($target['title'] ?? '', $target['description'] ?? '');
        $scored = [];
        foreach ($candidates as $cand) {
            $candVec = build_term_vector($cand['title'] ?? '', $cand['description'] ?? '');
            $sim = cosine_similarity($targetVec, $candVec);
            $cand['similarity'] = intval(round($sim * 100));
            $scored[] = $cand;
        }

        usort($scored, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($scored, 0, $limit);
    }

    /**
     * Build a crude term vector from text.
     */
    function build_term_vector(string $title, string $body): array
    {
        $stop = ['the','and','for','with','from','this','that','have','are','was','were','but','not','you','your','about','into','onto','over','under','into','near','very','just','can','cannot','will','would','should','could'];
        $text = strtolower($title . ' ' . $body);
        $text = preg_replace('/[^a-z0-9 ]/', ' ', $text);
        $parts = array_filter(explode(' ', $text), function ($token) use ($stop) {
            return strlen($token) >= 3 && !in_array($token, $stop, true);
        });
        return array_count_values($parts);
    }

    /**
     * Cosine similarity between two sparse term-frequency vectors.
     */
    function cosine_similarity(array $vecA, array $vecB): float
    {
        if (empty($vecA) || empty($vecB)) {
            return 0.0;
        }
        $dot = 0.0;
        foreach ($vecA as $term => $freqA) {
            if (isset($vecB[$term])) {
                $dot += $freqA * $vecB[$term];
            }
        }
        $magA = sqrt(array_sum(array_map(function ($v) { return $v * $v; }, $vecA)));
        $magB = sqrt(array_sum(array_map(function ($v) { return $v * $v; }, $vecB)));
        if ($magA == 0.0 || $magB == 0.0) {
            return 0.0;
        }
        return $dot / ($magA * $magB);
    }
}
