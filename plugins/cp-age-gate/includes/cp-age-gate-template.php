<?php

// exit if age gate not enabled
if ( false == get_theme_mod( 'cp_setting_age_gate_enabled' ) ) {
    return;
}

// get logo from Age Gate customizer
// use the site logo if it does not exist
$logo = get_theme_mod( 'cp_setting_age_gate_logo', '' );
if(!$logo) {
    $logo = get_theme_mod( 'cp_setting_logo_header', '' );
}

$background_image = get_theme_mod( 'cp_setting_age_gate_background_image', '' );

$content = get_theme_mod('cp_setting_age_gate_content', '<p>Are you over 21 years of age?</p>');
$yes_text = get_theme_mod('cp_setting_age_gate_yes_text', 'Yes');
$no_text = get_theme_mod('cp_setting_age_gate_no_text', 'No');

?>

<div id="cp-age-gate" class="cp-age-gate">
    <div class="cp-age-gate-content">
        <?php if($logo && $logo['url']) : ?>
			<img class="cp-age-gate-logo" src="<?php echo $logo['url']; ?>" alt="<?php bloginfo('name'); ?>" />
		<?php endif; ?>
        <?php echo $content; ?>
        <div class="cp-age-gate-buttons">
            <button id="cp-age-gate-button-yes" class="btn-primary" data-cp-age-gate-button="yes"><?php echo $yes_text; ?></button>
            <button id="cp-age-gate-button-no" class="btn-primary-outline" data-cp-age-gate-button="no"><?php echo $no_text; ?></button>
        </div>
        <p id="cp-age-gate-warning" class="cp-age-gate-warning">You are not old enough to view this website.</p>
    </div>
    <?php if($background_image) : ?>
        <?php echo wp_get_attachment_image($background_image, 'full', false, [
			'class' => 'cp-age-gate-background-image position-50-50'
		]); ?>
    <?php endif; ?>
</div>