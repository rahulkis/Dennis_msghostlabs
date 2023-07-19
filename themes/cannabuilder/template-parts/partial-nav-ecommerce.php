<?php if ( class_exists( 'WooCommerce' ) ) : ?>

	<?php
		$search = get_theme_mod( 'cp_setting_header_search', '' );
		$cart = get_theme_mod( 'cp_setting_header_show_cart', true );
		$account = get_theme_mod( 'cp_setting_header_show_account', true );
	?>

	<div class="site-nav-actions">

		<?php if($search) : ?>

			<?php if(shortcode_exists('fibosearch')) : ?>

				<?php echo do_shortcode('[fibosearch]'); ?>

			<?php else : ?>

				<div class="site-nav-search site-header-item">
					<button class="btn-reset" type="button" data-a11y-dialog-show="modal-search"><span class="sr-only">search website</span><svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 22"><path fill="#404040" d="M20.713 18.158l-4.09-4.09a.984.984 0 00-.697-.287h-.668a8.49 8.49 0 001.805-5.25A8.53 8.53 0 008.53 0 8.53 8.53 0 000 8.531a8.53 8.53 0 008.531 8.531 8.49 8.49 0 005.25-1.804v.668c0 .263.103.513.287.698l4.09 4.089a.98.98 0 001.39 0l1.16-1.16a.989.989 0 00.005-1.395zM8.53 13.78c-2.9 0-5.25-2.346-5.25-5.25 0-2.9 2.346-5.25 5.25-5.25 2.9 0 5.25 2.346 5.25 5.25 0 2.9-2.346 5.25-5.25 5.25z"/></svg></button>
				</div>

			<?php endif; ?>

		<?php endif; ?>

		<?php if($account) : ?>
			<div class="site-nav-account site-header-item">
				<a href="<?php echo wc_get_page_permalink( 'myaccount' ); ?>"><span class="sr-only">my account</span><svg xmlns="http://www.w3.org/2000/svg" width="21" height="21"><path fill="#404040" d="M10.5 0C4.725 0 0 4.725 0 10.5S4.725 21 10.5 21 21 16.275 21 10.5 16.275 0 10.5 0zm0 3.15c1.785 0 3.15 1.365 3.15 3.15s-1.365 3.15-3.15 3.15S7.35 8.085 7.35 6.3s1.365-3.15 3.15-3.15zm0 14.91c-2.625 0-4.935-1.365-6.3-3.36 0-2.1 4.2-3.255 6.3-3.255 2.1 0 6.3 1.155 6.3 3.255-1.365 1.995-3.675 3.36-6.3 3.36z"></path></svg></a>
			</div>
		<?php endif; ?>

		<?php if($cart) : ?>
			<div class="site-nav-cart site-header-item">
				<a class="site-nav-cart-link cfw-side-cart-open-trigger" href="<?php echo wc_get_cart_url(); ?>">
				<span class="sr-only">view cart</span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="21" height="21" viewBox="0 0 21 21"><defs><path id="carticon" d="M1435.4 99.9a2.1 2.1 0 1 1-4.2 0 2.1 2.1 0 0 1 4.2 0zm-4.93-18.9l.94 2.1h15.54c.63 0 1.05.42 1.05 1.05 0 .21 0 .32-.21.53l-3.78 6.82a1.9 1.9 0 0 1-1.78 1.05h-7.77l-.95 1.78v.11c0 .1.11.21.21.21h12.18v2.1h-12.6a2.1 2.1 0 0 1-2.1-2.1 4 4 0 0 1 .21-1.05l1.47-2.52-3.78-7.98h-2.1V81zm15.43 18.9a2.1 2.1 0 1 1-4.2 0 2.1 2.1 0 0 1 4.2 0z"/></defs><g transform="translate(-1427 -81)"><use fill="#404040" xlink:href="#carticon"/></g></svg>
					<span class="site-nav-cart-total"></span>
				</a>
			</div>
		<?php endif; ?>

	</div>

<?php endif; ?>