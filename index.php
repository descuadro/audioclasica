<?php
/**
 * Main Template File
 *
 * @package themeHandle
 */
get_header(); ?>

<section class="feed" <?php if ( is_home() ): ?>id="home-feed"<?php endif ?>>
	<?php if (is_category()): ?>

		<header>
			<h1><?php single_cat_title( '', true ); ?></h1>
		</header>

	<?php endif ?>

	<!-- Loop -->
	<?php get_template_part( 'loop', 'index' ); ?>


	<nav class="pagination">
		<?php # posts_nav_link(' ... ', 'Anterior', 'Siguiente'); ?>
		<!-- New infinite scroll -->
		<p id="scroll"><img src="<?php bloginfo('template_directory'); ?>/assets/images/loading.gif" /> Cargando más historias...</p>
	</nav>

</section>

<!-- #primary -->
<?php get_footer(); ?>

<!-- #images widgets -->

<div style="display:none;">
<?php $sidebar_id = ( is_category() ) ? sanitize_title( get_cat_name( get_query_var( 'cat' ) ) ) . '-sidebar' : 'sidebar-2';
dynamic_sidebar( $sidebar_id ); ?>
</div>



<!-- #image widgets .js -->

<script>
	var feed = $('.feed'),
			sidebar = $('aside.sidebar'),
			breakpoint = 1023;

			mobileBanner1 = feed.find('.banner-holder').eq(0),
			mobileBanner2 = feed.find('.banner-holder').eq(1),
			mobileBanner3 = feed.find('.banner-holder').eq(2),

			sidebarBanner1 = sidebar.find('.banner-holder').eq(0),
			sidebarBanner2 = sidebar.find('.banner-holder').eq(1),
			sidebarBanner3 = sidebar.find('.banner-holder').eq(2);

			enquire.register("screen and (max-width: 1023px)", {
		    match : function() {
		      mobileBanner1.load(' #extra-1', function() {
						$(this).imagesLoaded( function() {
							picturefill();
						});
					});

					mobileBanner2.load('<?php bloginfo('url'); ?> #extra-2', function() {
						$(this).imagesLoaded( function() {
							picturefill();
						});
					});

					mobileBanner3.load('<?php bloginfo('url'); ?> #extra-3', function() {
						$(this).imagesLoaded( function() {
							picturefill();
						});
					});
		    },
		    unmatch : function() {
		      mobileBanner1.add(mobileBanner2).add(mobileBanner3).find('.extra').remove();
		    }
			});

			enquire.register("screen and (min-width: 1023px)", {
		    match : function() {
		      sidebarBanner1.load(' #extra-1', function() {
						$(this).imagesLoaded( function() {
							picturefill();
						});
					});

					sidebarBanner2.load('<?php bloginfo('url'); ?> #extra-2', function() {
						$(this).imagesLoaded( function() {
							picturefill();
						});
					});

					sidebarBanner3.load('<?php bloginfo('url'); ?> #extra-3', function() {
						$(this).imagesLoaded( function() {
							picturefill();
						});
					});
		    },
		    unmatch : function() {
		      sidebarBanner1.add(sidebarBanner2).add(sidebarBanner3).find('.extra').remove();
		    }
			});
</script>