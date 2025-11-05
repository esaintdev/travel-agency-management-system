<?php
class CountryAPI {
    private $db;
    private $cache_duration = 86400; // 24 hours
    
    public function __construct($database = null) {
        $this->db = $database;
    }
    
    /**
     * Get all countries from REST Countries API
     */
    public function getAllCountries() {
        $cache_key = 'all_countries';
        
        // Check cache first
        if ($this->db && $cached = $this->getFromCache($cache_key)) {
            return $cached;
        }
        
        $url = 'https://restcountries.com/v3.1/all?fields=name,cca2,cca3,flag,flags,capital,population,region,subregion';
        $countries = $this->fetchAPI($url);
        
        if ($countries) {
            // Sort countries alphabetically by common name
            usort($countries, function($a, $b) {
                return strcmp($a['name']['common'], $b['name']['common']);
            });
            
            // Cache the result
            if ($this->db) {
                $this->saveToCache($cache_key, $countries);
            }
        }
        
        return $countries ?: $this->getFallbackCountries();
    }
    
    /**
     * Get specific country details
     */
    public function getCountryDetails($countryCode) {
        $cache_key = "country_details_{$countryCode}";
        
        // Check cache first
        if ($this->db && $cached = $this->getFromCache($cache_key)) {
            return $cached;
        }
        
        $url = "https://restcountries.com/v3.1/alpha/{$countryCode}";
        $country = $this->fetchAPI($url);
        
        if ($country && isset($country[0])) {
            $countryData = $country[0];
            
            // Get additional data
            $countryData['attractions'] = $this->getTouristAttractions($countryData['name']['common']);
            $countryData['images'] = $this->getCountryImages($countryData['name']['common']);
            $countryData['description'] = $this->getCountryDescription($countryData['name']['common']);
            
            // Cache the result
            if ($this->db) {
                $this->saveToCache($cache_key, $countryData);
            }
            
            return $countryData;
        }
        
        return null;
    }
    
    /**
     * Get tourist attractions and cities
     */
    private function getTouristAttractions($countryName) {
        $attractions = [];
        
        // Get major cities using REST Countries API or GeoDB Cities API
        $cities = $this->getMajorCities($countryName);
        if (!empty($cities)) {
            $attractions['Major Cities'] = $cities;
        }
        
        // Add curated attractions for popular countries
        $curatedAttractions = $this->getCuratedAttractions($countryName);
        if (!empty($curatedAttractions)) {
            $attractions = array_merge($attractions, $curatedAttractions);
        }
        
        // Default attractions if nothing found
        if (empty($attractions)) {
            $attractions = [
                'Popular Destinations' => [
                    'Capital City',
                    'Historic Sites',
                    'Natural Landmarks',
                    'Cultural Centers'
                ]
            ];
        }
        
        return $attractions;
    }
    
    /**
     * Get major cities for a country
     */
    private function getMajorCities($countryName) {
        // Try GeoDB Cities API (free tier available)
        $cities = $this->getGeoCities($countryName);
        
        if (empty($cities)) {
            // Fallback to curated major cities
            $cities = $this->getCuratedCities($countryName);
        }
        
        return array_slice($cities, 0, 5); // Limit to 5 cities
    }
    
    /**
     * Get cities from GeoDB Cities API
     */
    private function getGeoCities($countryName) {
        // Free tier: 1000 requests/day
        $url = "https://wft-geo-db.p.rapidapi.com/v1/geo/countries/" . urlencode($countryName) . "/cities?limit=5&sort=-population";
        
        // You can get a free API key from RapidAPI
        $rapidapi_key = 'YOUR_RAPIDAPI_KEY'; // Replace with your key
        
        if ($rapidapi_key === 'YOUR_RAPIDAPI_KEY') {
            return [];
        }
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'header' => [
                    "X-RapidAPI-Host: wft-geo-db.p.rapidapi.com",
                    "X-RapidAPI-Key: " . $rapidapi_key
                ]
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return [];
        }
        
        $data = json_decode($response, true);
        $cities = [];
        
        if (isset($data['data'])) {
            foreach ($data['data'] as $city) {
                $cities[] = $city['name'];
            }
        }
        
        return $cities;
    }
    
    /**
     * Get curated major cities as fallback
     */
    private function getCuratedCities($countryName) {
        $curatedCities = [
            'United States' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix'],
            'United Kingdom' => ['London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool'],
            'France' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice'],
            'Germany' => ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt'],
            'Italy' => ['Rome', 'Milan', 'Naples', 'Turin', 'Florence'],
            'Spain' => ['Madrid', 'Barcelona', 'Valencia', 'Seville', 'Bilbao'],
            'Japan' => ['Tokyo', 'Osaka', 'Kyoto', 'Yokohama', 'Kobe'],
            'Australia' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide'],
            'Canada' => ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Ottawa'],
            'Brazil' => ['São Paulo', 'Rio de Janeiro', 'Brasília', 'Salvador', 'Fortaleza'],
            'India' => ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Kolkata'],
            'China' => ['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu'],
            'Russia' => ['Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan'],
            'Mexico' => ['Mexico City', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana'],
            'South Africa' => ['Cape Town', 'Johannesburg', 'Durban', 'Pretoria', 'Port Elizabeth'],
            'Egypt' => ['Cairo', 'Alexandria', 'Giza', 'Luxor', 'Aswan'],
            'Turkey' => ['Istanbul', 'Ankara', 'Izmir', 'Antalya', 'Bursa'],
            'Thailand' => ['Bangkok', 'Chiang Mai', 'Phuket', 'Pattaya', 'Krabi'],
            'Greece' => ['Athens', 'Thessaloniki', 'Patras', 'Heraklion', 'Rhodes'],
            'Netherlands' => ['Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven']
        ];
        
        return $curatedCities[$countryName] ?? [];
    }
    
    /**
     * Get curated attractions for popular countries
     */
    private function getCuratedAttractions($countryName) {
        $attractions = [
            'United States' => [
                'Famous Landmarks' => ['Statue of Liberty', 'Grand Canyon', 'Golden Gate Bridge', 'Mount Rushmore'],
                'National Parks' => ['Yellowstone', 'Yosemite', 'Grand Canyon', 'Zion']
            ],
            'France' => [
                'Historic Sites' => ['Eiffel Tower', 'Louvre Museum', 'Notre-Dame', 'Palace of Versailles'],
                'Regions' => ['Provence', 'French Riviera', 'Loire Valley', 'Normandy']
            ],
            'Italy' => [
                'Historic Sites' => ['Colosseum', 'Vatican City', 'Leaning Tower of Pisa', 'Pompeii'],
                'Regions' => ['Tuscany', 'Amalfi Coast', 'Cinque Terre', 'Lake Como']
            ],
            'Japan' => [
                'Cultural Sites' => ['Mount Fuji', 'Fushimi Inari Shrine', 'Kinkaku-ji Temple', 'Hiroshima Peace Memorial'],
                'Modern Attractions' => ['Tokyo Skytree', 'Shibuya Crossing', 'Osaka Castle', 'Universal Studios Japan']
            ],
            'United Kingdom' => [
                'Historic Sites' => ['Big Ben', 'Tower of London', 'Stonehenge', 'Edinburgh Castle'],
                'Natural Beauty' => ['Lake District', 'Scottish Highlands', 'Cotswolds', 'Giant\'s Causeway']
            ],
            'Spain' => [
                'Historic Sites' => ['Sagrada Familia', 'Alhambra', 'Park Güell', 'Royal Palace of Madrid'],
                'Coastal Areas' => ['Costa del Sol', 'Costa Brava', 'Balearic Islands', 'Canary Islands']
            ],
            'Germany' => [
                'Historic Sites' => ['Brandenburg Gate', 'Neuschwanstein Castle', 'Cologne Cathedral', 'Berlin Wall'],
                'Regions' => ['Bavaria', 'Black Forest', 'Rhine Valley', 'Romantic Road']
            ],
            'Australia' => [
                'Natural Wonders' => ['Great Barrier Reef', 'Uluru', 'Blue Mountains', 'Twelve Apostles'],
                'Cities & Culture' => ['Sydney Opera House', 'Melbourne Laneways', 'Bondi Beach', 'Daintree Rainforest']
            ]
        ];
        
        return $attractions[$countryName] ?? [];
    }
    
    /**
     * Get country images from multiple sources
     */
    private function getCountryImages($countryName) {
        // Try multiple image sources
        $images = [];
        
        // 1. Try Pixabay API (free, no key required for basic use)
        $pixabayImages = $this->getPixabayImages($countryName);
        if (!empty($pixabayImages)) {
            $images = array_merge($images, $pixabayImages);
        }
        
        // 2. Try Pexels API (free with key)
        $pexelsImages = $this->getPexelsImages($countryName);
        if (!empty($pexelsImages)) {
            $images = array_merge($images, $pexelsImages);
        }
        
        // 3. Fallback to curated country images
        if (empty($images)) {
            $images = $this->getCuratedCountryImages($countryName);
        }
        
        return array_slice($images, 0, 6); // Limit to 6 images
    }
    
    /**
     * Get images from Pixabay (free API)
     */
    private function getPixabayImages($countryName) {
        $url = "https://pixabay.com/api/?key=9656065-a4094594c88b1f2df&q=" . urlencode($countryName . " tourism landmarks") . "&image_type=photo&category=places&per_page=3&safesearch=true";
        $response = $this->fetchAPI($url);
        
        $images = [];
        if (isset($response['hits'])) {
            foreach ($response['hits'] as $hit) {
                $images[] = [
                    'urls' => ['regular' => $hit['webformatURL']],
                    'alt_description' => $hit['tags'] ?? $countryName
                ];
            }
        }
        
        return $images;
    }
    
    /**
     * Get images from Pexels (free with API key)
     */
    private function getPexelsImages($countryName) {
        // You can get a free API key from https://www.pexels.com/api/
        $pexels_api_key = '9C0ITguyuiyznt3v8WNYSrDJ81nBAQq6pJkAv0TIghbt3SjmldWzOxZY'; // Your Pexels API key
        
        if ($pexels_api_key === 'YOUR_PEXELS_API_KEY') {
            return [];
        }
        
        $url = "https://api.pexels.com/v1/search?query=" . urlencode($countryName . " landmarks") . "&per_page=3";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'header' => "Authorization: " . $pexels_api_key
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return [];
        }
        
        $data = json_decode($response, true);
        $images = [];
        
        if (isset($data['photos'])) {
            foreach ($data['photos'] as $photo) {
                $images[] = [
                    'urls' => ['regular' => $photo['src']['medium']],
                    'alt_description' => $photo['alt'] ?? $countryName
                ];
            }
        }
        
        return $images;
    }
    
    /**
     * Get curated country images as fallback
     */
    private function getCuratedCountryImages($countryName) {
        // Curated high-quality images for popular destinations
        $curatedImages = [
            'United States' => [
                'https://images.unsplash.com/photo-1485738422979-f5c462d49f74?w=800',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'
            ],
            'France' => [
                'https://images.unsplash.com/photo-1502602898536-47ad22581b52?w=800',
                'https://images.unsplash.com/photo-1549144511-f099e773c147?w=800'
            ],
            'Japan' => [
                'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800',
                'https://images.unsplash.com/photo-1528164344705-47542687000d?w=800'
            ],
            'United Kingdom' => [
                'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=800',
                'https://images.unsplash.com/photo-1486299267070-83823f5448dd?w=800'
            ],
            'Germany' => [
                'https://images.unsplash.com/photo-1467269204594-9661b134dd2b?w=800',
                'https://images.unsplash.com/photo-1595867818082-083862f3d630?w=800'
            ],
            'Italy' => [
                'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800',
                'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800'
            ],
            'Spain' => [
                'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800',
                'https://images.unsplash.com/photo-1558642452-9d2a7deb7f62?w=800'
            ],
            'Australia' => [
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800'
            ]
        ];
        
        $images = [];
        if (isset($curatedImages[$countryName])) {
            foreach ($curatedImages[$countryName] as $imageUrl) {
                $images[] = [
                    'urls' => ['regular' => $imageUrl],
                    'alt_description' => $countryName . ' landmark'
                ];
            }
        } else {
            // Generic fallback
            $images[] = [
                'urls' => ['regular' => 'https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=800'],
                'alt_description' => $countryName . ' landscape'
            ];
        }
        
        return $images;
    }
    
    /**
     * Get country description from Wikipedia API
     */
    private function getCountryDescription($countryName) {
        $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode($countryName);
        $response = $this->fetchAPI($url);
        
        if ($response && isset($response['extract'])) {
            return $response['extract'];
        }
        
        return "Discover the beauty and culture of {$countryName}. A destination rich in history, natural wonders, and unique experiences waiting to be explored.";
    }
    
    /**
     * Fetch data from API with error handling
     */
    private function fetchAPI($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'M25TravelAgency/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("Failed to fetch API: {$url}");
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Get data from cache
     */
    private function getFromCache($key) {
        if (!$this->db) return null;
        
        try {
            $stmt = $this->db->prepare("SELECT data, created_at FROM api_cache WHERE cache_key = ? AND created_at > ?");
            $expiry = date('Y-m-d H:i:s', time() - $this->cache_duration);
            $stmt->execute([$key, $expiry]);
            
            if ($row = $stmt->fetch()) {
                return json_decode($row['data'], true);
            }
        } catch (Exception $e) {
            error_log("Cache read error: " . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Save data to cache
     */
    private function saveToCache($key, $data) {
        if (!$this->db) return;
        
        try {
            $stmt = $this->db->prepare("REPLACE INTO api_cache (cache_key, data, created_at) VALUES (?, ?, ?)");
            $stmt->execute([$key, json_encode($data), date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            error_log("Cache write error: " . $e->getMessage());
        }
    }
    
    /**
     * Fallback countries data if API fails
     */
    private function getFallbackCountries() {
        return [
            [
                'name' => ['common' => 'United States'],
                'cca2' => 'US',
                'flags' => ['png' => 'https://flagcdn.com/w320/us.png'],
                'capital' => ['Washington, D.C.'],
                'population' => 331900000,
                'region' => 'Americas'
            ],
            [
                'name' => ['common' => 'United Kingdom'],
                'cca2' => 'GB',
                'flags' => ['png' => 'https://flagcdn.com/w320/gb.png'],
                'capital' => ['London'],
                'population' => 67800000,
                'region' => 'Europe'
            ],
            [
                'name' => ['common' => 'France'],
                'cca2' => 'FR',
                'flags' => ['png' => 'https://flagcdn.com/w320/fr.png'],
                'capital' => ['Paris'],
                'population' => 67400000,
                'region' => 'Europe'
            ],
            [
                'name' => ['common' => 'Germany'],
                'cca2' => 'DE',
                'flags' => ['png' => 'https://flagcdn.com/w320/de.png'],
                'capital' => ['Berlin'],
                'population' => 83200000,
                'region' => 'Europe'
            ],
            [
                'name' => ['common' => 'Japan'],
                'cca2' => 'JP',
                'flags' => ['png' => 'https://flagcdn.com/w320/jp.png'],
                'capital' => ['Tokyo'],
                'population' => 125800000,
                'region' => 'Asia'
            ],
            [
                'name' => ['common' => 'Australia'],
                'cca2' => 'AU',
                'flags' => ['png' => 'https://flagcdn.com/w320/au.png'],
                'capital' => ['Canberra'],
                'population' => 25700000,
                'region' => 'Oceania'
            ]
        ];
    }
    
    /**
     * Create cache table if it doesn't exist
     */
    public function createCacheTable() {
        if (!$this->db) return;
        
        try {
            $sql = "CREATE TABLE IF NOT EXISTS api_cache (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cache_key VARCHAR(255) UNIQUE NOT NULL,
                data LONGTEXT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_cache_key (cache_key),
                INDEX idx_created_at (created_at)
            )";
            
            $this->db->exec($sql);
        } catch (Exception $e) {
            error_log("Failed to create cache table: " . $e->getMessage());
        }
    }
}
?>
