<?php
/**
 * Custom Tiny MCE Style
 */
function cannabuilder_custom_mce_buttons( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    return $buttons;
}

add_filter('mce_buttons_2', 'cannabuilder_custom_mce_buttons');

/**
 * Define styles to show in formats dropdown
 */
function cannabuilder_before_init_insert_formats( $init_array ) {  
    // Define the style_formats array
    $style_formats = array(  
        // Each array child is a format with it's own settings
        array(  
            'title' => 'Primary Button',  
            'selector' => 'a',  
            'classes' => 'btn-primary'             
        ),
        array(  
            'title' => 'White Button',  
            'selector' => 'a',  
            'classes' => 'btn-white'             
        ),
        array(  
            'title' => 'Accent 1 Button',  
            'selector' => 'a',  
            'classes' => 'btn-accent-1'             
        ),
        array(  
            'title' => 'Accent 2 Button',  
            'selector' => 'a',  
            'classes' => 'btn-accent-2'             
        ),
        array(  
            'title' => 'Accent 3 Button',  
            'selector' => 'a',  
            'classes' => 'btn-accent-3'             
        ),
        array(  
            'title' => 'Accent 4 Button',  
            'selector' => 'a',  
            'classes' => 'btn-accent-4'             
        )
    );

    // Insert the array, JSON ENCODED, into 'style_formats'
    $init_array['style_formats'] = json_encode( $style_formats );  

    return $init_array;

}

add_filter( 'tiny_mce_before_init', 'cannabuilder_before_init_insert_formats' );

/**
 * Add editor styles
 */
function cannabuilder_add_editor_styles() {
    add_editor_style( get_template_directory_uri() . '/dist/css/custom-editor-styles.css?v=1.0.2' );
}

add_action( 'init', 'cannabuilder_add_editor_styles' );