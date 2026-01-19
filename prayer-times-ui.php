<?php
/**
 * Prayer Times UI Shortcode
 * Displays interactive prayer times with location selector, tabs, and navigation
 * Usage: [prayer_times_ui]
 */

class PrayerTimesUI {
    private $api_base = 'https://api.aladhan.com/v1';
    
    public function __construct() {
        add_shortcode('prayer_times_ui', [$this, 'display_prayer_times_ui']);
        add_action('wp_ajax_ui_get_cities', [$this, 'ajax_get_cities']);
        add_action('wp_ajax_nopriv_ui_get_cities', [$this, 'ajax_get_cities']);
        add_action('wp_ajax_ui_get_prayer_times', [$this, 'ajax_get_prayer_times']);
        add_action('wp_ajax_nopriv_ui_get_prayer_times', [$this, 'ajax_get_prayer_times']);
        add_action('wp_ajax_ui_get_month_prayer_times', [$this, 'ajax_get_month_prayer_times']);
        add_action('wp_ajax_nopriv_ui_get_month_prayer_times', [$this, 'ajax_get_month_prayer_times']);
        add_action('wp_ajax_ui_clear_cache', [$this, 'ajax_clear_cache']);
        add_action('wp_ajax_nopriv_ui_clear_cache', [$this, 'ajax_clear_cache']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Clear all prayer times cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('prayer_times_nonce', 'nonce');
        
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%prayer_times%'");
        
        wp_send_json_success(['message' => 'Cache cleared']);
    }
    
    /**
     * Enqueue styles and scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_style('prayer-times-ui-style', plugin_dir_url(__FILE__) . 'css/prayer-times-ui.css');
        wp_enqueue_script('prayer-times-ui-script', plugin_dir_url(__FILE__) . 'js/prayer-times-ui.js', ['jquery'], '1.0', true);
        wp_localize_script('prayer-times-ui-script', 'prayerTimesAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('prayer_times_nonce')
        ]);
    }
    
    /**
     * Get Hijri date
     */
    public function get_hijri_date($date = null) {
        if ($date === null) {
            $date = date('d-m-Y');
        }
        
        $response = wp_remote_get($this->api_base . '/gToH?date=' . $date);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data']['hijri'])) {
            return false;
        }
        
        $hijri = $data['data']['hijri'];
        return [
            'day' => $hijri['day'],
            'month' => $hijri['month']['en'],
            'year' => $hijri['year']
        ];
    }
    
    /**
     * AJAX: Get cities by search term from local JSON file
     */
    public function ajax_get_cities() {
        check_ajax_referer('prayer_times_nonce', 'nonce');
        
        $search = sanitize_text_field($_GET['search'] ?? '');
        
        if (strlen($search) < 1) {
            wp_send_json_error('Search term too short');
        }
        
        // Load countries JSON file
        $json_file = plugin_dir_path(__FILE__) . 'data/countries.json';
        if (!file_exists($json_file)) {
            wp_send_json_error('Countries data not found');
        }
        
        $json_content = file_get_contents($json_file);
        $data = json_decode($json_content, true);
        
        $cities = [];
        $search_lower = strtolower($search);
        
        if (isset($data['countries']) && is_array($data['countries'])) {
            foreach ($data['countries'] as $country_data) {
                $country_name = $country_data['name'] ?? '';
                
                // Check if country name matches search
                if (stripos($country_name, $search) !== false) {
                    // Add first city of matching country
                    if (isset($country_data['cities'][0])) {
                        $cities[] = [
                            'name' => $country_data['cities'][0],
                            'country' => $country_name,
                            'timezone' => $country_data['timezone'] ?? ''
                        ];
                    }
                } else if (isset($country_data['cities']) && is_array($country_data['cities'])) {
                    // Check if any city matches search
                    foreach ($country_data['cities'] as $city) {
                        if (stripos($city, $search) !== false) {
                            $cities[] = [
                                'name' => $city,
                                'country' => $country_name,
                                'timezone' => $country_data['timezone'] ?? ''
                            ];
                        }
                    }
                }
                
                if (count($cities) >= 10) {
                    break;
                }
            }
        }
        
        wp_send_json_success($cities);
    }
    
    /**
     * AJAX: Get prayer times for a specific date and location
     */
    public function ajax_get_prayer_times() {
        check_ajax_referer('prayer_times_nonce', 'nonce');
        
        $city = sanitize_text_field($_POST['city'] ?? 'Dhaka');
        $country = sanitize_text_field($_POST['country'] ?? 'Bangladesh');
        $date = sanitize_text_field($_POST['date'] ?? date('Y-m-d'));
        
        // Convert date format from YYYY-MM-DD to DD-MM-YYYY for API
        $date_obj = DateTime::createFromFormat('Y-m-d', $date);
        $api_date = $date_obj ? $date_obj->format('d-m-Y') : date('d-m-Y');
        
        $transient_key = 'prayer_times_' . sanitize_title($city) . '_' . $date;
        // Skip cache to ensure always fresh data
        // $cached_data = get_transient($transient_key);
        // if ($cached_data !== false) {
        //     wp_send_json_success($cached_data);
        // }
        
        $params = [
            'city' => $city,
            'country' => $country,
            'date' => $api_date,
            'method' => 2,
            'school' => 0
        ];
        
        $response = wp_remote_get($this->api_base . '/timingsByCity?' . http_build_query($params));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Failed to fetch prayer times');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data']['timings'])) {
            wp_send_json_error('No prayer times data');
        }
        
        $timings = $data['data']['timings'];
        $hijri = $this->get_hijri_date($api_date);
        
        $prayer_data = [
            'date' => $date,
            'hijri' => $hijri ? $hijri['day'] . ' ' . $hijri['month'] . ', ' . $hijri['year'] : '',
            'prayers' => [
                [
                    'name' => 'Fajr',
                    'time' => substr($timings['Fajr'], 0, 5),
                    'icon' => '🌅'
                ],
                [
                    'name' => 'Sunrise',
                    'time' => substr($timings['Sunrise'], 0, 5),
                    'icon' => '☀️'
                ],
                [
                    'name' => 'Dhuhr',
                    'time' => substr($timings['Dhuhr'], 0, 5),
                    'icon' => '☀️'
                ],
                [
                    'name' => 'Asr',
                    'time' => substr($timings['Asr'], 0, 5),
                    'icon' => '☀️'
                ],
                [
                    'name' => 'Maghrib',
                    'time' => substr($timings['Maghrib'], 0, 5),
                    'icon' => '🌆'
                ],
                [
                    'name' => 'Isha',
                    'time' => substr($timings['Isha'], 0, 5),
                    'icon' => '🌙'
                ]
            ]
        ];
        
        set_transient($transient_key, $prayer_data, HOUR_IN_SECONDS);
        
        wp_send_json_success($prayer_data);
    }
    
    /**
     * AJAX: Get prayer times for entire month
     */
    public function ajax_get_month_prayer_times() {
        check_ajax_referer('prayer_times_nonce', 'nonce');
        
        $city = sanitize_text_field($_POST['city'] ?? 'Dhaka');
        $country = sanitize_text_field($_POST['country'] ?? 'Bangladesh');
        $year = intval($_POST['year'] ?? date('Y'));
        $month = intval($_POST['month'] ?? date('m'));
        
        $transient_key = 'prayer_times_month_' . sanitize_title($city) . '_' . $year . '_' . $month;
        $cached_data = get_transient($transient_key);
        
        // Skip cache for debugging - cache will be 12 hours
        // if ($cached_data !== false) {
        //     wp_send_json_success($cached_data);
        // }
        
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $month_data = [];
        $today_date = date('Y-m-d');
        
        for ($day = 1; $day <= $days_in_month; $day++) {
            // Format date as YYYY-MM-DD
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            // Format date as DD-MM-YYYY for API
            $date_formatted = date('d-m-Y', strtotime($date));
            
            $params = [
                'city' => $city,
                'country' => $country,
                'date' => $date_formatted,
                'method' => 2,
                'school' => 0
            ];
            
            $response = wp_remote_get($this->api_base . '/timingsByCity?' . http_build_query($params));
            
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                
                if (isset($data['data']['timings'])) {
                    $timings = $data['data']['timings'];
                    $hijri = $this->get_hijri_date($date_formatted);
                    $is_today = ($date === $today_date);
                    
                    $month_data[] = [
                        'day' => $day,
                        'date' => $date,
                        'day_name' => date('D', strtotime($date)),
                        'hijri_day' => $hijri ? $hijri['day'] : '',
                        'hijri_month' => $hijri ? $hijri['month'] : '',
                        'is_today' => $is_today,
                        'prayers' => [
                            'Fajr' => substr($timings['Fajr'], 0, 5),
                            'Sunrise' => substr($timings['Sunrise'], 0, 5),
                            'Dhuhr' => substr($timings['Dhuhr'], 0, 5),
                            'Asr' => substr($timings['Asr'], 0, 5),
                            'Maghrib' => substr($timings['Maghrib'], 0, 5),
                            'Isha' => substr($timings['Isha'], 0, 5)
                        ]
                    ];
                }
            }
        }
        
        set_transient($transient_key, $month_data, HOUR_IN_SECONDS);
        
        wp_send_json_success($month_data);
    }
    
    /**
     * Display the main UI
     */
    public function display_prayer_times_ui($atts = []) {
        $today = date('Y-m-d');
        $hijri = $this->get_hijri_date();
        $hijri_display = $hijri ? $hijri['day'] . ' ' . $hijri['month'] . ', ' . $hijri['year'] : '';
        
        ob_start();
        ?>
        <div class="prayer-times-container">
            <!-- Header with Location Selector -->
            <div class="prayer-times-header">
                <div class="prayer-times-title">
                    <h2>Prayer Times</h2>
                </div>
                <div class="prayer-times-location">
                    <div class="hijri-date-display"><?php echo esc_html($hijri_display); ?></div>
                    <div class="gregorian-date-display"><?php echo esc_html(date('jS F, Y')); ?></div>
                    <button class="btn-select-location" data-toggle="modal" data-target="#locationModal">
                        📍 Select Location
                    </button>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="prayer-times-tabs">
                <button class="tab-btn active" data-tab="today">Today</button>
                <button class="tab-btn" data-tab="month">Month</button>
            </div>
            
            <!-- Today Tab -->
            <div class="tab-content active" id="tab-today">
                <div class="prayer-times-navigation">
                    <button class="btn-prev" id="prevDay">← Previous</button>
                    <span class="current-date" id="currentDate"><?php echo esc_html(date('D, M j')); ?></span>
                    <button class="btn-next" id="nextDay">Next →</button>
                </div>
                <div class="prayer-times-list" id="todayPrayerTimes">
                    <p>Loading prayer times...</p>
                </div>
            </div>
            
            <!-- Month Tab -->
            <div class="tab-content" id="tab-month">
                <div class="month-header">
                    <h3 id="monthTitle"><?php echo esc_html(date('F Y')); ?></h3>
                </div>
                <div class="month-table" id="monthPrayerTimes">
                    <p>Loading month prayer times...</p>
                </div>
            </div>
            
            <!-- Location Modal -->
            <div class="modal" id="locationModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Enter Location</h3>
                        <button class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Specify a location to display prayer times</p>
                        <input type="text" id="citySearch" class="city-input" placeholder="Search city...">
                        <div class="city-suggestions" id="citySuggestions"></div>
                        <input type="hidden" id="selectedCity" value="Dhaka">
                        <input type="hidden" id="selectedCountry" value="Bangladesh">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-clear" id="btnClear">Clear</button>
                        <button class="btn btn-select" id="btnSelect">Select</button>
                        <button class="btn btn-close" id="btnClose">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            let currentDate = new Date();
            let selectedCity = 'Dhaka';
            let selectedCountry = 'Bangladesh';
            
            // Load initial prayer times
            loadPrayerTimes();
            loadMonthPrayerTimes();
            
            // Tab switching
            $('.tab-btn').on('click', function() {
                const tabName = $(this).data('tab');
                $('.tab-btn').removeClass('active');
                $('.tab-content').removeClass('active');
                $(this).addClass('active');
                $('#tab-' + tabName).addClass('active');
                
                if (tabName === 'month') {
                    loadMonthPrayerTimes();
                }
            });
            
            // Navigation buttons
            $('#prevDay').on('click', function() {
                currentDate.setDate(currentDate.getDate() - 1);
                updateDateDisplay();
                loadPrayerTimes();
            });
            
            $('#nextDay').on('click', function() {
                currentDate.setDate(currentDate.getDate() + 1);
                updateDateDisplay();
                loadPrayerTimes();
            });
            
            // Location modal
            $('.btn-select-location').on('click', function() {
                $('#locationModal').addClass('show');
            });
            
            $('#btnClose, .modal-close').on('click', function() {
                $('#locationModal').removeClass('show');
            });
            
            $('#btnClear').on('click', function() {
                $('#citySearch').val('');
                $('#citySuggestions').empty();
                $('#selectedCity').val('');
                $('#selectedCountry').val('');
            });
            
            $('#btnSelect').on('click', function() {
                selectedCity = $('#selectedCity').val() || 'Dhaka';
                selectedCountry = $('#selectedCountry').val() || 'Bangladesh';
                currentDate = new Date();
                updateDateDisplay();
                loadPrayerTimes();
                loadMonthPrayerTimes();
                $('#locationModal').removeClass('show');
            });
            
            // City search with suggestions
            $('#citySearch').on('input', function() {
                const searchTerm = $(this).val();
                
                if (searchTerm.length < 2) {
                    $('#citySuggestions').empty();
                    return;
                }
                
                $.ajax({
                    url: prayerTimesAjax.ajaxurl,
                    type: 'GET',
                    data: {
                        action: 'ui_get_cities',
                        search: searchTerm,
                        nonce: prayerTimesAjax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            let html = '';
                            response.data.forEach(function(city) {
                                html += '<div class="suggestion-item" data-city="' + city.name + '" data-country="' + city.country + '">';
                                html += city.name + ', ' + city.country;
                                html += '</div>';
                            });
                            $('#citySuggestions').html(html);
                            
                            $('.suggestion-item').on('click', function() {
                                const city = $(this).data('city');
                                const country = $(this).data('country');
                                $('#citySearch').val(city);
                                $('#selectedCity').val(city);
                                $('#selectedCountry').val(country);
                                $('#citySuggestions').empty();
                            });
                        }
                    }
                });
            });
            
            // Load prayer times
            function loadPrayerTimes() {
                const dateStr = currentDate.toISOString().split('T')[0];
                
                $.ajax({
                    url: prayerTimesAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ui_get_prayer_times',
                        city: selectedCity,
                        country: selectedCountry,
                        date: dateStr,
                        nonce: prayerTimesAjax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            renderPrayerTimes(response.data);
                        }
                    }
                });
            }
            
            // Load month prayer times
            function loadMonthPrayerTimes() {
                $.ajax({
                    url: prayerTimesAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ui_get_month_prayer_times',
                        city: selectedCity,
                        country: selectedCountry,
                        year: currentDate.getFullYear(),
                        month: currentDate.getMonth() + 1,
                        nonce: prayerTimesAjax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            renderMonthPrayerTimes(response.data);
                        }
                    }
                });
            }
            
            // Render prayer times
            function renderPrayerTimes(data) {
                let html = '<div class="prayer-cards">';
                
                data.prayers.forEach(function(prayer) {
                    html += '<div class="prayer-card">';
                    html += '<div class="prayer-icon">' + prayer.icon + '</div>';
                    html += '<div class="prayer-name">' + prayer.name + '</div>';
                    html += '<div class="prayer-time">' + prayer.time + '</div>';
                    html += '</div>';
                });
                
                html += '</div>';
                $('#todayPrayerTimes').html(html);
            }
            
            // Render month prayer times
            function renderMonthPrayerTimes(data) {
                let html = '<table class="month-prayer-table"><thead><tr>';
                html += '<th>Date</th><th>Hijri</th><th>Fajr</th><th>Sunrise</th><th>Dhuhr</th><th>Asr</th><th>Maghrib</th><th>Isha</th>';
                html += '</tr></thead><tbody>';
                
                data.forEach(function(day) {
                    const todayClass = day.is_today ? ' today-row' : '';
                    html += '<tr class="' + todayClass + '">';
                    html += '<td>' + day.day_name + ' ' + day.day + '</td>';
                    html += '<td>' + day.hijri_day + ' ' + day.hijri_month + '</td>';
                    html += '<td>' + day.prayers.Fajr + '</td>';
                    html += '<td>' + day.prayers.Sunrise + '</td>';
                    html += '<td>' + day.prayers.Dhuhr + '</td>';
                    html += '<td>' + day.prayers.Asr + '</td>';
                    html += '<td>' + day.prayers.Maghrib + '</td>';
                    html += '<td>' + day.prayers.Isha + '</td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#monthPrayerTimes').html(html);
            }
            
            // Update date display
            function updateDateDisplay() {
                const options = { weekday: 'short', month: 'short', day: 'numeric' };
                const dateStr = currentDate.toLocaleDateString('en-US', options);
                $('#currentDate').text(dateStr);
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
}

// Initialize the shortcode
new PrayerTimesUI();
