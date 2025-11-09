<?php
class AISEOOptimizer {
    private $db;
    private $config;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
        $this->loadConfig();
    }
    
    private function loadConfig() {
        try {
            $stmt = $this->db->query("SELECT setting_name, setting_value, setting_type FROM seo_config");
            $this->config = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $value = $row['setting_value'];
                if ($row['setting_type'] === 'number') {
                    $value = (float)$value;
                } elseif ($row['setting_type'] === 'boolean') {
                    $value = $value === 'true';
                } elseif ($row['setting_type'] === 'json') {
                    $value = json_decode($value, true);
                }
                $this->config[$row['setting_name']] = $value;
            }
        } catch (PDOException $e) {
            $this->config = $this->getDefaultConfig();
        }
    }
    
    private function getDefaultConfig() {
        return [
            'focus_keyword_weight' => 25,
            'content_length_weight' => 15,
            'readability_weight' => 20,
            'meta_optimization_weight' => 20,
            'heading_structure_weight' => 10,
            'image_optimization_weight' => 10,
            'min_content_length' => 300,
            'max_content_length' => 2500,
            'optimal_keyword_density' => 1.5,
            'max_keyword_density' => 3.0,
            'meta_title_min_length' => 30,
            'meta_title_max_length' => 60,
            'meta_description_min_length' => 120,
            'meta_description_max_length' => 160
        ];
    }
    
    public function analyzeContent($title, $content, $excerpt = '', $focus_keyword = '', $meta_title = '', $meta_description = '') {
        $analysis = [
            'seo_score' => 0,
            'readability_score' => 0,
            'keyword_density' => 0,
            'word_count' => 0,
            'issues' => [],
            'suggestions' => [],
            'keyword_analysis' => [],
            'content_analysis' => [],
            'meta_analysis' => []
        ];
        
        // Clean content for analysis
        $clean_content = strip_tags($content);
        $words = str_word_count($clean_content, 1);
        $analysis['word_count'] = count($words);
        
        // Analyze content length
        $analysis['content_analysis']['length'] = $this->analyzeContentLength($analysis['word_count']);
        
        // Analyze readability
        $analysis['readability_score'] = $this->calculateReadabilityScore($clean_content);
        $analysis['content_analysis']['readability'] = $this->analyzeReadability($analysis['readability_score']);
        
        // Analyze focus keyword if provided
        if (!empty($focus_keyword)) {
            $analysis['keyword_density'] = $this->calculateKeywordDensity($clean_content, $focus_keyword);
            $analysis['keyword_analysis'] = $this->analyzeKeyword($title, $clean_content, $excerpt, $focus_keyword);
        }
        
        // Analyze meta tags
        $analysis['meta_analysis'] = $this->analyzeMetaTags($meta_title, $meta_description, $focus_keyword);
        
        // Analyze heading structure
        $analysis['content_analysis']['headings'] = $this->analyzeHeadingStructure($content);
        
        // Analyze images
        $analysis['content_analysis']['images'] = $this->analyzeImages($content);
        
        // Calculate overall SEO score
        $analysis['seo_score'] = $this->calculateSEOScore($analysis);
        
        // Generate suggestions
        $analysis['suggestions'] = $this->generateSuggestions($analysis, $focus_keyword);
        
        return $analysis;
    }
    
    private function analyzeContentLength($word_count) {
        $min_length = $this->config['min_content_length'];
        $max_length = $this->config['max_content_length'];
        
        $score = 0;
        $status = 'poor';
        $message = '';
        
        if ($word_count < $min_length) {
            $score = ($word_count / $min_length) * 50;
            $status = 'poor';
            $message = "Content is too short. Add " . ($min_length - $word_count) . " more words.";
        } elseif ($word_count > $max_length) {
            $score = 75;
            $status = 'good';
            $message = "Content is quite long. Consider breaking it into multiple posts.";
        } else {
            $optimal_range = ($min_length + $max_length) / 2;
            $distance_from_optimal = abs($word_count - $optimal_range);
            $score = 100 - ($distance_from_optimal / $optimal_range * 25);
            $status = $score >= 80 ? 'excellent' : ($score >= 60 ? 'good' : 'fair');
            $message = "Content length is " . $status . ".";
        }
        
        return [
            'score' => round($score),
            'status' => $status,
            'message' => $message,
            'word_count' => $word_count
        ];
    }
    
    private function calculateReadabilityScore($text) {
        // Flesch Reading Ease Score implementation
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $sentence_count = count($sentences);
        
        if ($sentence_count === 0) return 0;
        
        $words = str_word_count($text, 1);
        $word_count = count($words);
        
        if ($word_count === 0) return 0;
        
        // Count syllables
        $syllable_count = 0;
        foreach ($words as $word) {
            $syllable_count += $this->countSyllables(strtolower($word));
        }
        
        // Flesch Reading Ease formula
        $avg_sentence_length = $word_count / $sentence_count;
        $avg_syllables_per_word = $syllable_count / $word_count;
        
        $flesch_score = 206.835 - (1.015 * $avg_sentence_length) - (84.6 * $avg_syllables_per_word);
        
        // Convert to 0-100 scale
        return max(0, min(100, $flesch_score));
    }
    
    private function countSyllables($word) {
        $word = preg_replace('/[^a-z]/', '', $word);
        if (strlen($word) <= 3) return 1;
        
        $vowels = 'aeiouy';
        $syllables = 0;
        $prev_was_vowel = false;
        
        for ($i = 0; $i < strlen($word); $i++) {
            $is_vowel = strpos($vowels, $word[$i]) !== false;
            if ($is_vowel && !$prev_was_vowel) {
                $syllables++;
            }
            $prev_was_vowel = $is_vowel;
        }
        
        // Handle silent 'e'
        if (substr($word, -1) === 'e' && $syllables > 1) {
            $syllables--;
        }
        
        return max(1, $syllables);
    }
    
    private function calculateKeywordDensity($content, $keyword) {
        $words = str_word_count(strtolower($content), 1);
        $total_words = count($words);
        
        if ($total_words === 0) return 0;
        
        $keyword_lower = strtolower($keyword);
        $keyword_count = 0;
        
        // Count exact matches
        foreach ($words as $word) {
            if ($word === $keyword_lower) {
                $keyword_count++;
            }
        }
        
        // Count phrase matches
        if (strpos($keyword, ' ') !== false) {
            $content_lower = strtolower($content);
            $keyword_count += substr_count($content_lower, $keyword_lower);
        }
        
        return ($keyword_count / $total_words) * 100;
    }
    
    private function analyzeKeyword($title, $content, $excerpt, $focus_keyword) {
        $keyword_lower = strtolower($focus_keyword);
        $title_lower = strtolower($title);
        $content_lower = strtolower($content);
        $excerpt_lower = strtolower($excerpt);
        
        $analysis = [
            'in_title' => strpos($title_lower, $keyword_lower) !== false,
            'in_content' => strpos($content_lower, $keyword_lower) !== false,
            'in_excerpt' => strpos($excerpt_lower, $keyword_lower) !== false,
            'density' => $this->calculateKeywordDensity($content, $focus_keyword),
            'prominence_score' => 0
        ];
        
        // Calculate prominence score
        $score = 0;
        if ($analysis['in_title']) $score += 30;
        if ($analysis['in_content']) $score += 20;
        if ($analysis['in_excerpt']) $score += 15;
        
        // Density scoring
        $density = $analysis['density'];
        $optimal_density = $this->config['optimal_keyword_density'];
        $max_density = $this->config['max_keyword_density'];
        
        if ($density >= $optimal_density && $density <= $max_density) {
            $score += 35;
        } elseif ($density > 0 && $density < $optimal_density) {
            $score += ($density / $optimal_density) * 35;
        } elseif ($density > $max_density) {
            $score += 20; // Penalty for over-optimization
        }
        
        $analysis['prominence_score'] = round($score);
        
        return $analysis;
    }
    
    private function analyzeMetaTags($meta_title, $meta_description, $focus_keyword = '') {
        $analysis = [
            'title' => $this->analyzeMetaTitle($meta_title, $focus_keyword),
            'description' => $this->analyzeMetaDescription($meta_description, $focus_keyword)
        ];
        
        return $analysis;
    }
    
    private function analyzeMetaTitle($meta_title, $focus_keyword = '') {
        $length = strlen($meta_title);
        $min_length = $this->config['meta_title_min_length'];
        $max_length = $this->config['meta_title_max_length'];
        
        $score = 0;
        $issues = [];
        
        if (empty($meta_title)) {
            $issues[] = 'Meta title is missing';
        } else {
            if ($length < $min_length) {
                $issues[] = "Meta title is too short ({$length} chars). Recommended: {$min_length}-{$max_length} chars.";
                $score = ($length / $min_length) * 50;
            } elseif ($length > $max_length) {
                $issues[] = "Meta title is too long ({$length} chars). Recommended: {$min_length}-{$max_length} chars.";
                $score = 70;
            } else {
                $score = 90;
            }
            
            if (!empty($focus_keyword) && strpos(strtolower($meta_title), strtolower($focus_keyword)) === false) {
                $issues[] = 'Focus keyword not found in meta title';
                $score -= 20;
            } else {
                $score += 10;
            }
        }
        
        return [
            'score' => max(0, round($score)),
            'length' => $length,
            'issues' => $issues
        ];
    }
    
    private function analyzeMetaDescription($meta_description, $focus_keyword = '') {
        $length = strlen($meta_description);
        $min_length = $this->config['meta_description_min_length'];
        $max_length = $this->config['meta_description_max_length'];
        
        $score = 0;
        $issues = [];
        
        if (empty($meta_description)) {
            $issues[] = 'Meta description is missing';
        } else {
            if ($length < $min_length) {
                $issues[] = "Meta description is too short ({$length} chars). Recommended: {$min_length}-{$max_length} chars.";
                $score = ($length / $min_length) * 50;
            } elseif ($length > $max_length) {
                $issues[] = "Meta description is too long ({$length} chars). Recommended: {$min_length}-{$max_length} chars.";
                $score = 70;
            } else {
                $score = 90;
            }
            
            if (!empty($focus_keyword) && strpos(strtolower($meta_description), strtolower($focus_keyword)) === false) {
                $issues[] = 'Focus keyword not found in meta description';
                $score -= 15;
            } else {
                $score += 10;
            }
        }
        
        return [
            'score' => max(0, round($score)),
            'length' => $length,
            'issues' => $issues
        ];
    }
    
    private function analyzeHeadingStructure($content) {
        $headings = [];
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $headings[] = [
                'level' => (int)$match[1],
                'text' => strip_tags($match[2])
            ];
        }
        
        $score = 0;
        $issues = [];
        
        if (empty($headings)) {
            $issues[] = 'No headings found in content';
        } else {
            $score = 50;
            
            // Check for H1
            $has_h1 = false;
            foreach ($headings as $heading) {
                if ($heading['level'] === 1) {
                    $has_h1 = true;
                    break;
                }
            }
            
            if ($has_h1) {
                $score += 25;
            } else {
                $issues[] = 'No H1 heading found';
            }
            
            // Check hierarchy
            $prev_level = 0;
            $hierarchy_good = true;
            foreach ($headings as $heading) {
                if ($prev_level > 0 && $heading['level'] > $prev_level + 1) {
                    $hierarchy_good = false;
                    break;
                }
                $prev_level = $heading['level'];
            }
            
            if ($hierarchy_good) {
                $score += 25;
            } else {
                $issues[] = 'Heading hierarchy is not proper';
            }
        }
        
        return [
            'score' => $score,
            'headings' => $headings,
            'issues' => $issues
        ];
    }
    
    private function analyzeImages($content) {
        preg_match_all('/<img[^>]+>/i', $content, $matches);
        $images = $matches[0];
        
        $total_images = count($images);
        $images_with_alt = 0;
        $issues = [];
        
        foreach ($images as $img) {
            if (preg_match('/alt\s*=\s*["\'][^"\']*["\']/', $img)) {
                $images_with_alt++;
            }
        }
        
        $score = 0;
        if ($total_images === 0) {
            $score = 50; // Neutral score for no images
        } else {
            $alt_percentage = ($images_with_alt / $total_images) * 100;
            $score = $alt_percentage;
            
            if ($images_with_alt < $total_images) {
                $missing = $total_images - $images_with_alt;
                $issues[] = "{$missing} image(s) missing alt text";
            }
        }
        
        return [
            'score' => round($score),
            'total_images' => $total_images,
            'images_with_alt' => $images_with_alt,
            'issues' => $issues
        ];
    }
    
    private function calculateSEOScore($analysis) {
        $weights = [
            'focus_keyword_weight' => $this->config['focus_keyword_weight'],
            'content_length_weight' => $this->config['content_length_weight'],
            'readability_weight' => $this->config['readability_weight'],
            'meta_optimization_weight' => $this->config['meta_optimization_weight'],
            'heading_structure_weight' => $this->config['heading_structure_weight'],
            'image_optimization_weight' => $this->config['image_optimization_weight']
        ];
        
        $scores = [
            'keyword' => isset($analysis['keyword_analysis']['prominence_score']) ? $analysis['keyword_analysis']['prominence_score'] : 0,
            'content_length' => $analysis['content_analysis']['length']['score'],
            'readability' => $analysis['readability_score'],
            'meta' => ($analysis['meta_analysis']['title']['score'] + $analysis['meta_analysis']['description']['score']) / 2,
            'headings' => $analysis['content_analysis']['headings']['score'],
            'images' => $analysis['content_analysis']['images']['score']
        ];
        
        $weighted_score = 0;
        $total_weight = 0;
        
        $weighted_score += $scores['keyword'] * ($weights['focus_keyword_weight'] / 100);
        $weighted_score += $scores['content_length'] * ($weights['content_length_weight'] / 100);
        $weighted_score += $scores['readability'] * ($weights['readability_weight'] / 100);
        $weighted_score += $scores['meta'] * ($weights['meta_optimization_weight'] / 100);
        $weighted_score += $scores['headings'] * ($weights['heading_structure_weight'] / 100);
        $weighted_score += $scores['images'] * ($weights['image_optimization_weight'] / 100);
        
        return round($weighted_score);
    }
    
    private function generateSuggestions($analysis, $focus_keyword = '') {
        $suggestions = [];
        
        // Content length suggestions
        if ($analysis['content_analysis']['length']['score'] < 70) {
            $suggestions[] = [
                'type' => 'content',
                'priority' => 'high',
                'message' => $analysis['content_analysis']['length']['message']
            ];
        }
        
        // Readability suggestions
        if ($analysis['readability_score'] < 60) {
            $suggestions[] = [
                'type' => 'readability',
                'priority' => 'medium',
                'message' => 'Improve readability by using shorter sentences and simpler words.'
            ];
        }
        
        // Keyword suggestions
        if (!empty($focus_keyword) && isset($analysis['keyword_analysis'])) {
            $ka = $analysis['keyword_analysis'];
            if (!$ka['in_title']) {
                $suggestions[] = [
                    'type' => 'keyword',
                    'priority' => 'high',
                    'message' => 'Include the focus keyword in the title.'
                ];
            }
            
            if ($ka['density'] < 0.5) {
                $suggestions[] = [
                    'type' => 'keyword',
                    'priority' => 'medium',
                    'message' => 'Increase keyword density by mentioning the focus keyword more often.'
                ];
            } elseif ($ka['density'] > 3) {
                $suggestions[] = [
                    'type' => 'keyword',
                    'priority' => 'high',
                    'message' => 'Reduce keyword density to avoid over-optimization.'
                ];
            }
        }
        
        // Meta tag suggestions
        foreach ($analysis['meta_analysis']['title']['issues'] as $issue) {
            $suggestions[] = [
                'type' => 'meta',
                'priority' => 'high',
                'message' => $issue
            ];
        }
        
        foreach ($analysis['meta_analysis']['description']['issues'] as $issue) {
            $suggestions[] = [
                'type' => 'meta',
                'priority' => 'high',
                'message' => $issue
            ];
        }
        
        // Heading suggestions
        foreach ($analysis['content_analysis']['headings']['issues'] as $issue) {
            $suggestions[] = [
                'type' => 'structure',
                'priority' => 'medium',
                'message' => $issue
            ];
        }
        
        // Image suggestions
        foreach ($analysis['content_analysis']['images']['issues'] as $issue) {
            $suggestions[] = [
                'type' => 'images',
                'priority' => 'medium',
                'message' => $issue
            ];
        }
        
        return $suggestions;
    }
    
    public function saveAnalysis($page_type, $page_id, $analysis_data, $meta_data = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO seo_analysis 
                (page_type, page_id, focus_keyword, seo_score, readability_score, keyword_density, word_count, 
                 meta_title, meta_description, analysis_data, suggestions, last_analyzed) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                focus_keyword = VALUES(focus_keyword),
                seo_score = VALUES(seo_score),
                readability_score = VALUES(readability_score),
                keyword_density = VALUES(keyword_density),
                word_count = VALUES(word_count),
                meta_title = VALUES(meta_title),
                meta_description = VALUES(meta_description),
                analysis_data = VALUES(analysis_data),
                suggestions = VALUES(suggestions),
                last_analyzed = NOW()
            ");
            
            $stmt->execute([
                $page_type,
                $page_id,
                $meta_data['focus_keyword'] ?? '',
                $analysis_data['seo_score'],
                $analysis_data['readability_score'],
                $analysis_data['keyword_density'],
                $analysis_data['word_count'],
                $meta_data['meta_title'] ?? '',
                $meta_data['meta_description'] ?? '',
                json_encode($analysis_data),
                json_encode($analysis_data['suggestions'])
            ]);
            
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getAnalysis($page_type, $page_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM seo_analysis WHERE page_type = ? AND page_id = ?");
            $stmt->execute([$page_type, $page_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $result['analysis_data'] = json_decode($result['analysis_data'], true);
                $result['suggestions'] = json_decode($result['suggestions'], true);
            }
            
            return $result;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function generateSchemaMarkup($page_type, $data) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['title'] ?? '',
            'description' => $data['excerpt'] ?? '',
            'author' => [
                '@type' => 'Person',
                'name' => $data['author'] ?? 'M25 Travel Agency'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->config['site_name'] ?? 'M25 Travel Agency',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $data['logo_url'] ?? ''
                ]
            ]
        ];
        
        if (isset($data['published_date'])) {
            $schema['datePublished'] = date('c', strtotime($data['published_date']));
        }
        
        if (isset($data['modified_date'])) {
            $schema['dateModified'] = date('c', strtotime($data['modified_date']));
        }
        
        if (isset($data['featured_image'])) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $data['featured_image']
            ];
        }
        
        return json_encode($schema, JSON_UNESCAPED_SLASHES);
    }
}
?>
