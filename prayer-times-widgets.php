<?php
class Prayer_Times_Widget {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    public function enqueue_assets() {
        // Only load on pages with the shortcode
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'prayer_times')) {
            // CSS
            // wp_enqueue_style(
            //     'prayer-times-style',
            //     PRAYER_TIMES_PLUGIN_URL . 'assets/css/style.css',
            //     array(),
            //     PRAYER_TIMES_VERSION
            // );
            
            // // JavaScript
            // wp_enqueue_script(
            //     'prayer-times-script',
            //     PRAYER_TIMES_PLUGIN_URL . 'assets/js/script.js',
            //     array(),
            //     PRAYER_TIMES_VERSION,
            //     array('in_footer' => true)
            // );
            
            // Localize script with data
            wp_localize_script('prayer-times-script', 'prayerTimesData', array(
                'countriesData' => $this->get_countries_data(),
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('prayer_times_nonce')
            ));
        }
    }
    
    private function get_countries_data() {
        $countries_file = plugin_dir_path(__FILE__) . 'data/countries.json';
        
        if (file_exists($countries_file)) {
            $countries_data = file_get_contents($countries_file);
            return json_decode($countries_data, true);
        }
        
        return array();
    }
    
    public function render_shortcode($atts) {
        ob_start();
        ?>
        <div id="prayer-times-widget"></div>
        <?php
        return ob_get_clean();
    }
}

// Register shortcode
add_shortcode('prayer_times', array(Prayer_Times_Widget::get_instance(), 'render_shortcode'));