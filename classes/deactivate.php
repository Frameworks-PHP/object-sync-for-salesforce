<?php

class Wordpress_Salesforce_Deactivate {

    protected $wpdb;
    protected $version;

    /**
    * What to do when the plugin is deactivated
    * @param string $version
    *
    */
    public function __construct( $wpdb, $version, $text_domain, $schedulable_classes ) {
        $this->wpdb = &$wpdb;
        $this->version = $version;
        $this->schedulable_classes = $schedulable_classes;
        register_deactivation_hook( dirname( __DIR__ ) . '/' . $text_domain . '.php', array( &$this, 'wordpress_salesforce_drop_tables' ) );
        register_deactivation_hook( dirname( __DIR__ ) . '/' . $text_domain . '.php', array( &$this, 'clear_schedule' ) );
        register_deactivation_hook( dirname( __DIR__ ) . '/' . $text_domain . '.php', array( &$this, 'delete_log_post_type' ) );
    }

    public function wordpress_salesforce_drop_tables() {
        $field_map_table = $this->wpdb->prefix . 'salesforce_field_map';
        $object_map_table = $this->wpdb->prefix . 'salesforce_object_map';
        $queue_table = $this->wpdb->prefix . 'queue';
        $failed_jobs_table = $this->wpdb->prefix . 'failed_jobs';
        $this->wpdb->query( 'DROP TABLE IF EXISTS ' . $field_map_table );
        $this->wpdb->query( 'DROP TABLE IF EXISTS ' . $object_map_table );
        $this->wpdb->query( 'DROP TABLE IF EXISTS ' . $queue_table );
        $this->wpdb->query( 'DROP TABLE IF EXISTS ' . $failed_jobs_table );
        delete_option( 'salesforce_rest_api_db_version' );
    }

    public function clear_schedule() {
        foreach ( $this->schedulable_classes as $class ) {
            wp_clear_scheduled_hook( $class['name'] );
        }
    }

    public function delete_log_post_type() {
        unregister_post_type( 'wp_log' );
    }

}