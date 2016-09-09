<?php

class Wordpress_Salesforce_Schedule extends WP_Job {

	protected $wpdb;
    protected $version;
    protected $login_credentials;
    protected $text_domain;
    protected $wordpress;
    protected $salesforce;
    protected $mappings;
    protected $schedule_name;
    protected $logging;

	/**
    * Functionality for syncing WordPress objects with Salesforce
    *
    * @param object $wpdb
    * @param string $version
    * @param array $login_credentials
    * @param string $text_domain
    * @param object $wordpress
    * @param object $salesforce
    * @param object $mappings
    * @param string $schedule_name
    * @throws \Exception
    */

    public function __construct( $wpdb, $version, $login_credentials, $text_domain, $wordpress, $salesforce, $mappings, $schedule_name, $logging, $schedulable_classes ) {
        
        $this->wpdb = &$wpdb;
        $this->version = $version;
        $this->login_credentials = $login_credentials;
        $this->text_domain = $text_domain; 
        $this->wordpress = $wordpress;
        $this->salesforce = $salesforce;
        $this->mappings = $mappings;
        $this->schedule_name = $schedule_name;
        $this->logging = $logging;
        $this->schedulable_classes = $schedulable_classes;

        $this->add_filters();
        //add_action( $this->schedule_name, array( $this, 'call_handler' ) ); // run the handle method

    }

    /**
    * Create the filters we need to run
    *
    */
    public function add_filters() {
		add_filter( 'cron_schedules', array( &$this, 'set_schedule_frequency' ) );
    }

    /**
    * Convert the schedule frequency from the admin settings into an array
    * interval must be in seconds for the class to use it
    *
    */
    public function set_schedule_frequency( $schedules ) {

        // create an option in the core schedules array for each one the plugin defines
        foreach ( $this->schedulable_classes as $class ) {
            $schedule_number = get_option( 'salesforce_api_' . $class['name'] . '_schedule_number', '' );
            $schedule_unit = get_option( 'salesforce_api_' . $class['name'] . '_schedule_unit', '' );

            switch ( $schedule_unit ) {
                case 'minutes':
                    $seconds = 60;
                    break;
                case 'hours':
                    $seconds = 3600;
                    break;
            }

            $key = $schedule_unit . '_' . $schedule_number;

            $schedules[$key] = array(
                'interval' => $seconds * $schedule_number,
                'display' => 'Every ' . $schedule_number . ' ' . $schedule_unit
            );

            $this->schedule_frequency = $key;

        }

		return $schedules;

    }

    /**
    * Convert the schedule frequency from the admin settings into an array
    * interval must be in seconds for the class to use it
    *
    */
    public function get_schedule_frequency_key( $name = '' ) {

    	$schedule_number = get_option( 'salesforce_api_' . $name . '_schedule_number', '' );
    	$schedule_unit = get_option( 'salesforce_api_' . $name . '_schedule_unit', '' );

		switch ( $schedule_unit ) {
			case 'minutes':
				$seconds = 60;
				break;
			case 'hours':
				$seconds = 3600;
				break;
		}

		$key = $schedule_unit . '_' . $schedule_number;

		return $key;

    }

    /**
     * Schedule function
     * This creates and manages the scheduling of the task
     *
     * @return void
     */
    public function use_schedule( $name = '' ) {
        
        if ( $name !== '' ) {
            $schedule_name = $name;
        } else {
            $schedule_name = $this->schedule_name;
        }

        $schedule_frequency = $this->get_schedule_frequency_key( $name );
    	
	    if (! wp_next_scheduled ( $schedule_name ) ) {
			wp_schedule_event( time(), $schedule_frequency, $schedule_name );
	    }

    }

	/**
     * Handle
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param mixed $item Queue item to iterate over
     *
     * @return mixed
     */
    public function handle() {
        error_log('foo');
        error_log('name is ' . $this->name );
    }

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();
		// we could log something here, or show something to admin user, etc.
	}

}