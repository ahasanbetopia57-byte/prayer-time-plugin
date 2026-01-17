<?php
/**
 * Plugin Name: Athan Pro Prayer Times
 * Description: Display Islamic prayer times with countdown
 * Version: 1.0.0
 */

// Main Prayer Times Class
class AthanProPrayerTimes {
    private $api_base = 'https://api.aladhan.com/v1';
    
    public function __construct() {
        add_shortcode('athan_today_times', [$this, 'display_today_times']);
        add_shortcode('athan_monthly_calendar', [$this, 'display_monthly_calendar']);
        add_shortcode('athan_countries', [$this, 'display_countries_list']);
        add_shortcode('athan_country_detail', [$this, 'display_country_detail']);
        add_shortcode('athan_city_detail', [$this, 'display_city_detail']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_get_cities', [$this, 'ajax_get_cities']);
        add_action('wp_ajax_nopriv_get_cities', [$this, 'ajax_get_cities']);
        add_action('wp_ajax_get_prayer_times', [$this, 'ajax_get_prayer_times']);
        add_action('wp_ajax_nopriv_get_prayer_times', [$this, 'ajax_get_prayer_times']);
        add_action('wp_ajax_get_monthly_calendar', [$this, 'ajax_get_monthly_calendar']);
        add_action('wp_ajax_nopriv_get_monthly_calendar', [$this, 'ajax_get_monthly_calendar']);
    }
    
    // Get prayer times for today
    public function get_today_times($city = 'Dhaka', $country = 'Bangladesh') {
        $transient_key = 'athan_today_' . sanitize_title($city) . '_' . date('Y-m-d');
        
        // Check cached data first
        $cached_data = get_transient($transient_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // API call for today's times
        $params = [
            'city' => $city,
            'country' => $country,
            'method' => 2, // ISNA method
            'school' => 0  // Shafi
        ];
        
        $response = wp_remote_get($this->api_base . '/timingsByCity?' . http_build_query($params));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($data['code'] == 200) {
            $times = [
                'Fajr' => $data['data']['timings']['Fajr'],
                'Sunrise' => $data['data']['timings']['Sunrise'],
                'Dhuhr' => $data['data']['timings']['Dhuhr'],
                'Asr' => $data['data']['timings']['Asr'],
                'Maghrib' => $data['data']['timings']['Maghrib'],
                'Isha' => $data['data']['timings']['Isha'],
                'hijri_date' => $data['data']['date']['hijri'],
                'gregorian_date' => $data['data']['date']['gregorian']
            ];
            
            // Cache for 1 hour
            set_transient($transient_key, $times, HOUR_IN_SECONDS);
            return $times;
        }
        
        return false;
    }
    
    // Get monthly calendar
    public function get_monthly_calendar($city = 'Dhaka', $country = 'Bangladesh', $month = null, $year = null) {
        $month = $month ?: date('n');
        $year = $year ?: date('Y');
        
        $transient_key = 'athan_monthly_' . sanitize_title($city) . '_' . $year . '_' . $month;
        
        $cached_data = get_transient($transient_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $params = [
            'city' => $city,
            'country' => $country,
            'method' => 2,
            'month' => $month,
            'year' => $year
        ];
        
        $response = wp_remote_get($this->api_base . '/calendarByCity?' . http_build_query($params));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($data['code'] == 200) {
            // Cache for 24 hours
            set_transient($transient_key, $data['data'], DAY_IN_SECONDS);
            return $data['data'];
        }
        
        return false;
    }
    
    // Display today's prayer times (Shortcode)
    public function display_today_times($atts) {
        $atts = shortcode_atts([
            'city' => 'Dhaka',
            'country' => 'Bangladesh'
        ], $atts);
        
        $times = $this->get_today_times($atts['city'], $atts['country']);
        
        if (!$times) {
            return '<div class="athan-error">Unable to load prayer times</div>';
        }
        
        ob_start();
        ?>
        <div class="athan-today-container">
            <div class="athan-header">
                <h2>Today</h2>
                <div class="next-prayer" id="athan-next-prayer">
                    Next prayer in <span id="athan-countdown">00:00:00</span>
                </div>
            </div>
            
            <div class="prayer-times-grid">
                <?php 
                $prayers = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
                foreach ($prayers as $prayer): 
                ?>
                <div class="prayer-time-item">
                    <span class="prayer-name"><?php echo esc_html($prayer); ?></span>
                    <span class="prayer-time" data-prayer="<?php echo strtolower($prayer); ?>">
                        <?php echo esc_html($times[$prayer]); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="athan-footer">
                <div class="location"><?php echo esc_html($atts['city'] . ', ' . $atts['country']); ?></div>
                <div class="dates">
                    <span class="hijri-date"><?php echo esc_html($times['hijri_date']['day'] . ' ' . 
                        $times['hijri_date']['month']['en'] . ', ' . $times['hijri_date']['year']); ?></span>
                    <span class="gregorian-date"><?php echo esc_html($times['gregorian_date']['date']); ?></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // Display monthly calendar (Shortcode)
    public function display_monthly_calendar($atts) {
        $atts = shortcode_atts([
            'city' => 'Dhaka',
            'country' => 'Bangladesh',
            'month' => date('n'),
            'year' => date('Y')
        ], $atts);
        
        $data = $this->get_monthly_calendar($atts['city'], $atts['country'], $atts['month'], $atts['year']);
        
        if (!$data) {
            return '<div class="athan-error">Unable to load calendar</div>';
        }
        
        ob_start();
        ?>
        <div class="athan-monthly-container">
            <h3><?php echo date('F Y', strtotime($atts['year'] . '-' . $atts['month'] . '-01')); ?></h3>
            
            <table class="athan-calendar-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Rajab</th>
                        <th>Fajr</th>
                        <th>Sunrise</th>
                        <th>Dhuhr</th>
                        <th>Asr</th>
                        <th>Maghrib</th>
                        <th>Isha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $day): ?>
                    <tr <?php echo date('j') == $day['date']['gregorian']['day'] ? 'class="today"' : ''; ?>>
                        <td><?php echo date('D', strtotime($day['date']['gregorian']['date'])); ?></td>
                        <td><?php echo esc_html($day['date']['hijri']['day']); ?></td>
                        <td><?php echo esc_html($day['timings']['Fajr']); ?></td>
                        <td><?php echo esc_html($day['timings']['Sunrise']); ?></td>
                        <td><?php echo esc_html($day['timings']['Dhuhr']); ?></td>
                        <td><?php echo esc_html($day['timings']['Asr']); ?></td>
                        <td><?php echo esc_html($day['timings']['Maghrib']); ?></td>
                        <td><?php echo esc_html($day['timings']['Isha']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }
    
    // Get countries data
    public function get_countries_data() {
        $file = plugin_dir_path(__FILE__) . 'data/countries.json';
        if (file_exists($file)) {
            $json = file_get_contents($file);
            return json_decode($json, true);
        }
        return false;
    }
    
    // Display countries list (Shortcode)
    public function display_countries_list($atts) {
        $countries_data = $this->get_countries_data();
        
        if (!$countries_data) {
            return '<div class="athan-error">Countries data not available</div>';
        }
        
        ob_start();
        ?>
        <div class="athan-countries-container">
            <div class="athan-countries-header">
                <h2>Country</h2>
                <p class="athan-subtitle">Discover accurate prayer timings for countries worldwide.</p>
                <div class="athan-search-box">
                    <input type="text" id="athan-countries-search" placeholder="Search countries..." />
                </div>
            </div>
            
            <div class="athan-countries-grid">
                <?php 
                // Sort countries alphabetically
                usort($countries_data['countries'], function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                
                // Group countries by first letter
                $grouped_countries = [];
                foreach ($countries_data['countries'] as $country) {
                    $first_letter = strtoupper(substr($country['name'], 0, 1));
                    if (!isset($grouped_countries[$first_letter])) {
                        $grouped_countries[$first_letter] = [];
                    }
                    $grouped_countries[$first_letter][] = $country;
                }
                
                // Sort by letter
                ksort($grouped_countries);
                
                // Render each letter group
                foreach ($grouped_countries as $letter => $countries): 
                ?>
                    <div class="athan-letter-column">
                        <h3 class="athan-letter-group"><?php echo esc_html($letter); ?></h3>
                        <div class="athan-countries-list">
                            <?php foreach ($countries as $country):
                                $offset = isset($country['offset']) ? $country['offset'] : '+00:00';
                                $capital = isset($country['capital']) ? $country['capital'] : '';
                            ?>
                                <a href="#" class="athan-country-item" data-country="<?php echo esc_attr($country['code']); ?>" data-country-name="<?php echo esc_attr($country['name']); ?>" data-capital="<?php echo esc_attr($capital); ?>">
                                    <span class="country-flag" id="flag-<?php echo esc_attr($country['code']); ?>">🌍</span>
                                    <div class="country-info">
                                        <span class="country-name"><?php echo esc_html($country['name']); ?></span>
                                        <span class="country-offset"><?php echo esc_html($offset); ?></span>
                                    </div>
                                    <span class="country-time" id="time-<?php echo esc_attr($country['code']); ?>">--:--</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <script>
            var athanCountriesData = <?php echo json_encode($countries_data['countries']); ?>;
        </script>
        <?php
        return ob_get_clean();
    }
    
    // Display country detail page (Shortcode)
    public function display_country_detail($atts) {
        // Get country from URL parameters if not in shortcode
        $country_code = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
        $country_name = isset($_GET['country_name']) ? sanitize_text_field($_GET['country_name']) : '';
        
        // Fallback to shortcode attributes
        if (empty($country_code)) {
            $country_code = isset($atts['country']) ? $atts['country'] : '';
        }
        if (empty($country_name)) {
            $country_name = isset($atts['country_name']) ? $atts['country_name'] : '';
        }
        
        if (empty($country_code) || empty($country_name)) {
            return '<div class="athan-error">Country not specified</div>';
        }
        
        $countries_data = $this->get_countries_data();
        $country_info = null;
        
        foreach ($countries_data['countries'] as $country) {
            if ($country['code'] === $country_code) {
                $country_info = $country;
                break;
            }
        }
        
        if (!$country_info) {
            return '<div class="athan-error">Country not found</div>';
        }
        
        ob_start();
        ?>
        <div class="athan-country-detail-container">
            <div class="athan-country-detail-header">
                <div class="athan-header-left">
                    <h1 id="athan-detail-country-name">Prayer Times In <?php echo esc_html($country_info['name']); ?></h1>
                    <p class="athan-header-time-info">
                        <span id="capital-current-time">--:--</span> • <span id="athan-detail-timezone"><?php echo esc_html($country_info['offset']); ?> GMT</span>
                    </p>
                </div>
                <div class="athan-header-right">
                    <div class="athan-country-flag" id="country-flag">🌍</div>
                    <div class="athan-country-emblem" id="country-emblem">🛡️</div>
                </div>
            </div>
            
            <div class="athan-cities-section">
                <h2 class="athan-cities-title">Payer Times For Cities In <?php echo esc_html($country_info['name']); ?></h2>
                
                <div class="athan-cities-search">
                    <input type="text" id="athan-cities-search" placeholder="Search cities..." />
                </div>
                
                <div class="athan-cities-table-wrapper">
                    <table class="athan-cities-prayer-table">
                        <thead>
                            <tr>
                                <th>City</th>
                                <th>Fajr</th>
                                <th>Sunrise</th>
                                <th>Dhuhr</th>
                                <th>Asr</th>
                                <th>Maghrib</th>
                                <th>Isha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($country_info['cities'] as $city): ?>
                            <tr class="athan-city-row" data-city="<?php echo esc_attr($city); ?>">
                                <td class="city-name-col"><a href="#" class="athan-city-link" data-city="<?php echo esc_attr($city); ?>" data-country="<?php echo esc_attr($country_info['name']); ?>" data-country-code="<?php echo esc_attr($country_code); ?>"><?php echo esc_html($city); ?></a></td>
                                <td class="fajr">--:--</td>
                                <td class="sunrise">--:--</td>
                                <td class="dhuhr">--:--</td>
                                <td class="asr">--:--</td>
                                <td class="maghrib">--:--</td>
                                <td class="isha">--:--</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="athan-back-link">
                    <a href="<?php echo esc_url(home_url('/countries/')); ?>" id="athan-back-to-countries">&larr; Back to Countries</a>
                </div>
            </div>
        </div>
        
        <script>
            var athanCountryDetail = <?php echo json_encode($country_info); ?>;
        </script>
        <?php
        return ob_get_clean();
    }
    
    // AJAX: Get cities for a country
    public function ajax_get_cities() {
        check_ajax_referer('athan_nonce', 'nonce');
        
        if (!isset($_POST['country'])) {
            wp_send_json_error('Country not specified');
        }
        
        $countries_data = $this->get_countries_data();
        $country_code = sanitize_text_field($_POST['country']);
        
        foreach ($countries_data['countries'] as $country) {
            if ($country['code'] === $country_code) {
                wp_send_json_success($country);
            }
        }
        
        wp_send_json_error('Country not found');
    }
    
    // AJAX: Get prayer times for cities
    public function ajax_get_prayer_times() {
        check_ajax_referer('athan_nonce', 'nonce');
        
        if (!isset($_POST['city']) || !isset($_POST['country'])) {
            wp_send_json_error('Missing parameters');
        }
        
        $city = sanitize_text_field($_POST['city']);
        $country = sanitize_text_field($_POST['country']);
        
        $times = $this->get_today_times($city, $country);
        
        if (!$times) {
            wp_send_json_error('Unable to fetch prayer times');
        }
        
        wp_send_json_success($times);
    }
    
    public function ajax_get_monthly_calendar() {
        check_ajax_referer('athan_nonce', 'nonce');
        
        if (!isset($_POST['city']) || !isset($_POST['country'])) {
            wp_send_json_error('Missing parameters');
        }
        
        $city = sanitize_text_field($_POST['city']);
        $country = sanitize_text_field($_POST['country']);
        $month = isset($_POST['month']) ? intval($_POST['month']) : date('n');
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        
        $calendar = $this->get_monthly_calendar($city, $country, $month, $year);
        
        if (!$calendar) {
            wp_send_json_error('Unable to fetch monthly calendar');
        }
        
        wp_send_json_success($calendar);
    }
    
    // Display city detail page (Shortcode)
    public function display_city_detail($atts) {
        // Get city from URL parameters
        $city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
        $country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
        $country_code = isset($_GET['country_code']) ? sanitize_text_field($_GET['country_code']) : '';
        
        if (empty($city) || empty($country)) {
            return '<div class="athan-error">City not specified</div>';
        }
        
        // Get countries data for flag and emblem
        $countries_data = $this->get_countries_data();
        
        ob_start();
        ?>
        <div class="athan-city-detail-container">
            <nav class="athan-breadcrumbs">
                <ol class="athan-breadcrumbs-list">
                    <li class="athan-breadcrumb-item">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="athan-breadcrumb-link">Home</a>
                    </li>
                    <li aria-hidden="true" class="athan-breadcrumb-separator">/</li>
                    <li class="athan-breadcrumb-item">
                        <a href="<?php echo esc_url(home_url('/countries')); ?>" class="athan-breadcrumb-link">Countries</a>
                    </li>
                    <li aria-hidden="true" class="athan-breadcrumb-separator">/</li>
                    <li class="athan-breadcrumb-item">
                        <a href="/countries-detail/?country=<?php echo esc_attr($country_code); ?>&country_name=<?php echo esc_attr($country); ?>" class="athan-breadcrumb-link"><?php echo esc_html($country); ?></a>
                    </li>
                    <li aria-hidden="true" class="athan-breadcrumb-separator">/</li>
                    <li class="athan-breadcrumb-item">
                        <span class="athan-breadcrumb-inactive"><?php echo esc_html($city); ?></span>
                    </li>
                </ol>
            </nav>
            
            <div class="athan-city-detail-header">
                <div class="athan-city-header-info">
                    <h1 id="athan-city-name">Prayer times in <?php echo esc_html($city); ?>, <?php echo esc_html($country); ?></h1>
                    <p class="athan-city-header-subtitle">
                        <span id="athan-city-time">--:--</span> • <span id="athan-city-offset">+00:00</span> GMT
                    </p>
                </div>
                <div class="athan-city-header-symbols">
                    <div class="athan-city-flag-container">
                        <img id="athan-city-flag-img" src="" alt="<?php echo esc_html($country); ?> flag" class="athan-city-flag-img" />
                    </div>
                    <div class="athan-city-emblem-container">
                        <img id="athan-city-emblem-img" src="" alt="<?php echo esc_html($country); ?> emblem" class="athan-city-emblem-img" />
                    </div>
                </div>
            </div>
            
            
            <div class="athan-city-prayer-info">
                <div class="athan-today-tab">
                    <span class="athan-today-label">Today</span>
                </div>
                <p class="athan-next-prayer">Next prayer in <span id="athan-next-prayer-countdown">00:00:00</span></p>
                
                <h2>Today's Prayer Times</h2>
                <p class="city-gregorian-date" id="athan-gregorian-date">--</p>
                <p class="city-hijri-date" id="athan-hijri-date">--</p>
                
                <div class="city-prayer-grid">
                    <div class="prayer-item">
                        <div class="prayer-item-header">
                            <span class="prayer-label">Fajr</span>
                            <span class="prayer-icon"><i class="fas fa-sun"></i></span>
                        </div>
                        <span class="prayer-time" id="athan-fajr">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <div class="prayer-item-header">
                            <span class="prayer-label">Sunrise</span>
                            <span class="prayer-icon"><i class="fas fa-cloud-sun"></i></span>
                        </div>
                        <span class="prayer-time" id="athan-sunrise">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <div class="prayer-item-header">
                            <span class="prayer-label">Dhuhr</span>
                            <span class="prayer-icon"><i class="fas fa-star"></i></span>
                        </div>
                        <span class="prayer-time" id="athan-dhuhr">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <div class="prayer-item-header">
                            <span class="prayer-label">Asr</span>
                            <span class="prayer-icon"><i class="fas fa-cloud-sun"></i></span>
                        </div>
                        <span class="prayer-time" id="athan-asr">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <div class="prayer-item-header">
                            <span class="prayer-label">Maghrib</span>
                            <span class="prayer-icon"><i class="fas fa-moon"></i></span>
                        </div>
                        <span class="prayer-time" id="athan-maghrib">--:--</span>
                    </div>
                    <div class="prayer-item">
                        <div class="prayer-item-header">
                            <span class="prayer-label">Isha</span>
                            <span class="prayer-icon"><i class="fas fa-moon"></i></span>
                        </div>
                        <span class="prayer-time" id="athan-isha">--:--</span>
                    </div>
                </div>
            </div>
            
            <div class="athan-city-monthly">
                <h2>Prayer Times In <?php echo esc_html($city); ?> For <span id="athan-month-year"><?php echo date('F'); ?></span> <span id="athan-hijri-month-name"></span></h2>
                <div id="athan-monthly-table-container">
                    <table class="athan-city-monthly-table">
                        <thead>
                            <tr>
                                <th><?php echo date('F'); ?></th>
                                <th>Hijri</th>
                                <th>Fajr</th>
                                <th>Sunrise</th>
                                <th>Dhuhr</th>
                                <th>Asr</th>
                                <th>Maghrib</th>
                                <th>Isha</th>
                            </tr>
                        </thead>
                        <tbody id="athan-monthly-tbody">
                            <tr>
                                <td colspan="8" class="loading">Loading prayer times...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <script>
            // Get country info from countries data
            var countryInfo = null;
            var countriesList = <?php echo json_encode($countries_data['countries']); ?>;
            for (var i = 0; i < countriesList.length; i++) {
                if (countriesList[i].code === '<?php echo esc_js($country_code); ?>') {
                    countryInfo = countriesList[i];
                    break;
                }
            }
            
            var athanCityDetail = {
                city: '<?php echo esc_js($city); ?>',
                country: '<?php echo esc_js($country); ?>',
                country_code: '<?php echo esc_js($country_code); ?>',
                offset: countryInfo ? countryInfo.offset : '+00:00',
                capital: countryInfo ? countryInfo.capital : ''
            };
            
            // Pass countries data to window
            if (typeof athanCountriesData === 'undefined') {
                var athanCountriesData = countriesList;
            }
            
            // Trigger city detail feature if jQuery is ready
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ready(function($) {
                    if (typeof initCityDetailFeature !== 'undefined') {
                        setTimeout(function() {
                            initCityDetailFeature();
                        }, 100);
                    }
                });
            }
        </script>
        <?php
        return ob_get_clean();
    }
    
    // Enqueue scripts and styles
    public function enqueue_scripts() {
        wp_enqueue_style('athan-pro-style', plugins_url('css/athan-style.css', __FILE__));
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        wp_enqueue_script('athan-pro-script', plugins_url('js/athan-script.js', __FILE__), ['jquery'], '1.0.0', true);
        
        // Localize script with prayer times for countdown and page URLs
        $today_times = $this->get_today_times();
        wp_localize_script('athan-pro-script', 'athanData', [
            'times' => $today_times ?: [],
            'timezone' => wp_timezone_string(),
            'nonce' => wp_create_nonce('athan_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'countriesUrl' => home_url('/countries/'),
            'countryDetailUrl' => home_url('/countries-detail/'),
            'cityDetailUrl' => home_url('/city-detail/')
        ]);
    }
}

new AthanProPrayerTimes();
?>