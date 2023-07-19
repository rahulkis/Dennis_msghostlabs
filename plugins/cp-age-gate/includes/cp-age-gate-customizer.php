<?php

add_action('plugins_loaded', 'cp_age_gate_customizer_settings');
function cp_age_gate_customizer_settings() {
    if(class_exists('Kirki')) {

        new \Kirki\Section(
            'cp_section_age_gate', [
            'title'          => esc_html__( 'Age Gate', 'kirki' ),
            'priority'       => 9999,
        ]);
        
        new \Kirki\Field\Checkbox_Switch(
            [
            'settings'    => 'cp_setting_age_gate_enabled',
            'label'       => esc_html__( 'Age Gate', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => '0',
            'priority'    => 10,
            'choices'     => [
                'on'  => esc_html__( 'Enable', 'kirki' ),
                'off' => esc_html__( 'Disable', 'kirki' ),
            ],
        ] );
    
        new \Kirki\Field\Image(
            [
            'settings'    => 'cp_setting_age_gate_logo',
            'label'       => esc_html__( 'Logo', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => '',
            'choices'     => [
                'save_as' => 'array',
            ],
        ] );
        
        new \Kirki\Field\Number(
            [
            'settings'    => 'cp_setting_age_gate_logo_width',
            'label'       => esc_html__( 'Logo Width', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => 350,
            'output'      => [
                [
                    'element' => '.cp-age-gate-logo',
                    'property' => 'width',
                    'units' => 'px'
                ],
            ],
        ] );

        new \Kirki\Field\Image(
            [
            'settings'    => 'cp_setting_age_gate_background_image',
            'label'       => esc_html__( 'Background Image', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => '',
            'choices'     => [
                'save_as' => 'id',
            ],
        ] );
        
        new \Kirki\Field\Color(
            [
            'settings'    => 'cp_setting_age_gate_background_color',
            'label'       => __( 'Background Color', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => '#ffffff',
            'output'      => [
                [
                    'element'  => '.cp-age-gate',
                    'property' => 'background-color',
                ]
            ]
        ] );
    
        new \Kirki\Field\Color(
            [
            'settings'    => 'cp_setting_age_gate_foreground_color',
            'label'       => __( 'Foreground Color', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => '#ffffff',
            'output'      => [
                [
                    'element'  => '.cp-age-gate-content',
                    'property' => 'background-color',
                ]
            ]
        ] );

        new \Kirki\Field\Number(
            [
            'settings'    => 'cp_setting_age_gate_foreground_width',
            'label'       => esc_html__( 'Foreground Width', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => 500,
            'output'      => [
                [
                    'element' => '.cp-age-gate-content',
                    'property' => 'max-width',
                    'units' => 'px'
                ],
            ],
        ] );
        
        new \Kirki\Field\Color(
            [
            'settings'    => 'cp_setting_age_gate_text_color',
            'label'       => __( 'Text Color', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => '',
            'output'      => [
                [
                    'element'  => '.cp-age-gate-content p, .cp-age-gate-content ul, .cp-age-gate-content ol, .cp-age-gate-content h1, .cp-age-gate-content h2, .cp-age-gate-content h3, .cp-age-gate-content h4, .cp-age-gate-content h5, .cp-age-gate-content h6',
                    'property' => 'color',
                ]
            ]
        ] );
        
        new \Kirki\Field\Editor(
            [
            'settings'    => 'cp_setting_age_gate_content',
            'label'       => esc_html__( 'Content', 'kirki' ),
            'section'     => 'cp_section_age_gate',
            'default'     => '<p>Are you over 21 years of age?</p>',
        ] );

        new \Kirki\Field\Text(
            [
            'settings' => 'cp_setting_age_gate_yes_text',
            'label'    => esc_html__( 'Yes Button Text', 'kirki' ),
            'section'  => 'cp_section_age_gate',
            'default'  => 'Yes'
        ] );

        new \Kirki\Field\Text(
            [
            'settings' => 'cp_setting_age_gate_no_text',
            'label'    => esc_html__( 'No Button Text', 'kirki' ),
            'section'  => 'cp_section_age_gate',
            'default'  => 'No'
        ] );
    
    }
}