<?php

add_action('admin_enqueue_scripts', 'cannabuilder_admin_theme_style');
add_action('login_enqueue_scripts', 'cannabuilder_admin_theme_style');
function cannabuilder_admin_theme_style() { ?>
	<style>
		.acf-accordion-title {
			background-color: #f7f7f7;
		}

		.acf-accordion-title label {
			font-size: 14px !important;
		}

		#poststuff .stuffbox>h3, #poststuff h2, #poststuff h3.hndle {
			font-size: 18px;
		}

		.toplevel_page_gf_edit_forms .column-view_count,
		.toplevel_page_gf_edit_forms .column-conversion {
			display: none;
		}

		/* .acf-field-flexible-content > .acf-label > label {
			display: none !important;
		}

		.acf-fc-layout-handle span > span {
			display: none !important;
		} */
	</style>
<?php }