<?php
/**
 * Hijri Date Shortcode
 * Displays the current Hijri date
 */

class HijriDateShortcode {
    private $api_base = 'https://api.aladhan.com/v1';
    
    public function __construct() {
        add_shortcode('hijri_date', [$this, 'display_hijri_date']);
    }
    
    /**
     * Get current Hijri date from API
     */
    public function get_hijri_date() {
        $transient_key = 'hijri_date_' . date('Y-m-d');
        
        // Check cached data first (cache for 24 hours)
        $cached_data = get_transient($transient_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // API call to get Hijri date
        $response = wp_remote_get($this->api_base . '/gToH?date=' . date('d-m-Y'));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['data']['hijri'])) {
            return false;
        }
        
        $hijri = $data['data']['hijri'];
        
        // Format the date
        $hijri_date = [
            'day' => $hijri['day'],
            'month' => $hijri['month']['en'],
            'year' => $hijri['year'],
            'formatted' => $hijri['day'] . ' ' . $hijri['month']['en'] . ', ' . $hijri['year']
        ];
        
        // Cache for 24 hours
        set_transient($transient_key, $hijri_date, DAY_IN_SECONDS);
        
        return $hijri_date;
    }
    
    /**
     * Display Hijri date shortcode
     * Usage: [hijri_date] or [hijri_date format="full"]
     */
    public function display_hijri_date($atts = []) {
        $atts = shortcode_atts([
            'format' => 'full',  // 'full', 'short', or 'year'
            'class' => 'hijri-date'
        ], $atts, 'hijri_date');
        
        $hijri_date = $this->get_hijri_date();
        
        if (!$hijri_date) {
            return '<span class="' . esc_attr($atts['class']) . ' error">Unable to fetch Hijri date</span>';
        }
        
        $output = '<span class="' . esc_attr($atts['class']) . '">';
        
        switch ($atts['format']) {
            case 'short':
                $output .= $hijri_date['day'] . ' ' . substr($hijri_date['month'], 0, 3) . ', ' . $hijri_date['year'];
                break;
            case 'year':
                $output .= $hijri_date['year'];
                break;
            case 'full':
            default:
                $output .= 'Hijri date ' . $hijri_date['formatted'];
                break;
        }
        
        $output .= '</span>';
        
        return $output;
    }
}

// Initialize the shortcode
new HijriDateShortcode();
