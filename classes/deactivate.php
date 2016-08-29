<?php

class Wordpress_Salesforce_Deactivate {

    protected $wpdb;
    protected $version;

    /**
    * What to do when the plugin is deactivated
    * @param string $version
    *
    * todo: deactivate the logging stuff as well
    *
    */
    public function __construct( $wpdb, $version, $text_domain, $schedule_name ) {
        $this->wpdb = &$wpdb;
        $this->version = $version;
        $this->schedule_name = $schedule_name;
        register_deactivation_hook( dirname( __DIR__ ) . '/' . $text_domain . '.php', array( &$this, 'wordpress_salesforce_drop_tables' ) );
        register_deactivation_hook( dirname( __DIR__ ) . '/' . $text_domain . '.php', array( &$this, 'clear_schedule' ) );
    }

    public function wordpress_salesforce_drop_tables() {
        $field_map_table = $this->wpdb->prefix . 'salesforce_field_map';
        $object_map_table = $this->wpdb->prefix . 'salesforce_object_map';
        $this->wpdb->query( 'DROP TABLE IF EXISTS ' . $field_map_table );
        $this->wpdb->query( 'DROP TABLE IF EXISTS ' . $object_map_table );
        delete_option( 'salesforce_rest_api_db_version' );
    }

    public function clear_schedule() {
        wp_clear_scheduled_hook( $this->schedule_name );
    }

}