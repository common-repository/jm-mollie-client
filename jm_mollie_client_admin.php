<?php

// Add a menu for our option page
// Hier niet nodig omdat galleriffic-bootstrap instellingen op de media pagina staan
// Draw the option page
// init text domain
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            ''.  __('Mollie Settings', 'jm_mollie_client'). '', 
            'manage_options', 
            'jm_mollie_client-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'jm_mollie_client_option_name' );
        ?>
        <div class="wrap">
            
            <h2>Mollie.nl </h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'jm_mollie_client_option_group' );   
                do_settings_sections( 'jm_mollie_client-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'jm_mollie_client_option_group', // Option group
            'jm_mollie_client_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            ''.  __('Settings', 'jm_mollie_client'). '', // Title
            array( $this, 'print_section_info' ), // Callback
            'jm_mollie_client-setting-admin' // Page
        );  

        add_settings_field(
            'apikey', // ID
            'API Key', // Title 
            array( $this, 'api_key_callback' ), // Callback
            'jm_mollie_client-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'idealonly', 
            ' '.  __('Only Ideal', 'jm_mollie_client'). '' , 
            array( $this, 'idealonly_callback' ), 
            'jm_mollie_client-setting-admin', 
            'setting_section_id'
        );
        add_settings_field(
            'checkin_description', 
            ' '.  __('Use Checkin as description', 'jm_mollie_client'). '' , 
            array( $this, 'checkin_description_callback' ), 
            'jm_mollie_client-setting-admin', 
            'setting_section_id'
        );     
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['apikey'] ) )
            $new_input['apikey'] = sanitize_text_field( $input['apikey'] );

        if( isset( $input['idealonly'] ) )
            $new_input['idealonly'] = absint( $input['idealonly'] );
        if( isset( $input['checkin_description'] ) )
            $new_input['checkin_description'] = absint( $input['checkin_description'] );       
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
          _e('Enter the settings below:', 'jm_mollie_client') ;
        
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="apikey" name="jm_mollie_client_option_name[apikey]" value="%s" />',
            isset( $this->options['apikey'] ) ? esc_attr( $this->options['apikey']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function idealonly_callback()
    {
        echo '<input type="checkbox" id="idealonly" name="jm_mollie_client_option_name[idealonly]" value="1" '. checked(1,$this->options['idealonly'], false) .' />';
        
    }
    public function checkin_description_callback()
    {
        echo '<input type="checkbox" id="checkin_description" name="jm_mollie_client_option_name[checkin_description]" value="1" '. checked(1,$this->options['checkin_description'], false) .' />';
        
    }    
}

if( is_admin() )
    $my_settings_page = new MySettingsPage();