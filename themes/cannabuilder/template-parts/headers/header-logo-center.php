<header id="masthead" class="site-header site-header-logo-center">

	<?php do_action('cp_before_header_wrap'); ?>

	<div class="site-header-wrap">
		
		<?php get_template_part('template-parts/partial', 'nav-branding'); ?>

		<nav id="site-nav" class="site-nav" role="navigation">

			<button class="site-nav-toggle" aria-controls="primary-menu" aria-label="Menu" aria-expanded="false">
				<span class="site-nav-toggle-text sr-only">Menu</span>
				<span aria-hidden="true" class="site-nav-toggle-bar site-nav-toggle-bar-top"></span>
				<span aria-hidden="true" class="site-nav-toggle-bar site-nav-toggle-bar-middle"></span>
				<span aria-hidden="true" class="site-nav-toggle-bar site-nav-toggle-bar-bottom"></span>
			</button>
				
			<div class="site-nav-wrap">
				<?php
					wp_nav_menu( array(
						'theme_location' => 'primary-left',
						'menu_id' => 'primary-menu-left',
						'menu_class' => 'site-nav-menu site-nav-menu-left',
						'container'	=>	'ul',
						'depth' => 2
					) );
				?>
				<?php
					wp_nav_menu( array(
						'theme_location' => 'primary-right',
						'menu_id' => 'primary-menu-right',
						'menu_class' => 'site-nav-menu site-nav-menu-right',
						'container'	=>	'ul',
						'depth' => 2
					) );
				?>
			</div>

		</nav><!-- #site-navigation -->

		<?php get_template_part('template-parts/partial', 'nav-ecommerce'); ?>

	</div>
		
</header><!-- #masthead -->