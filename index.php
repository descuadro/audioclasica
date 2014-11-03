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

	<?php while ( have_posts() ) : the_post(); ?>
		<?php
			if ( has_post_thumbnail() ) {
					$post_image_id = get_post_thumbnail_id($post->ID);

					if ($post_image_id) {
						$thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
						if ($thumbnail) (string)$thumbnail = $thumbnail[0];
					}
    	}
    	$post_cat = 'cat-'.get_first_category_ID();
    	$post_classes = array( 'story', $post_cat, );
		?>

		<?php #If first post, open featured loop ?>
		<?php if ( $wp_query->current_post == 0 ) { echo '<div class="featured-wrap">'."\n";  } ?>

		<?php #If even, open wrap ?>
		<?php if( $wp_query->current_post%2 == 1 ) echo  "\n".'<div class="wrap">'."\n" ; ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

				<?php #in principle, this isn't going in ?>
				<style> #post-<?php the_ID(); ?> { background-image: url('<?php echo $thumbnail; ?>'); } </style>

				<div class="image">
				<?php if (  has_post_thumbnail() ): ?>
					<?php the_post_thumbnail(); ?>
				<?php else: ?>
					<img src="http://placehold.it/520x245">
				<?php endif ?>
				</div>

				<header class="entry-header">
					<h6 class="story-details">
						<span class="light">Por</span>
						<span class="story-author"><?php the_author() ?></span>

						<time><?php the_date() ?></time>

						<div class="cat-title <?php single_cat_title( '', true ); ?>"><?php the_category('') ?></div>

					</h6>
					<h1 class="story-title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
				</header>

				<?php if ( $wp_query->current_post == 0 ): ?>
					<div class="body"><?php the_excerpt(); ?></div>
				<?php endif ?>

			</article>

		<?php #If odd and not first article, close wrap ?>
		<?php if( $wp_query->current_post%2 == 0 && !$wp_query->current_post == 0 || $wp_query->current_post == $wp_query->post_count-1 ) echo '</div><div class="banner-holder"></div>'."\n"; ?>

		<?php #If third post, close featured loop ?>
		<?php if( $wp_query->current_post == 2  ) echo '</div>'."\n"; ?>
	<?php endwhile; // end of the loop. ?>

	<nav class="pagination">
		<?php posts_nav_link(' ... ', 'Anterior', 'Siguiente'); ?>
	</nav>
</section>

<!-- #primary -->
<?php get_footer(); ?>

<!-- #images widgets -->

<div style="display:none;">
<?php
      if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar-2') ) : ?>
<?php endif; ?>
<?php
      if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar-3') ) : ?>
<?php endif; ?>
<?php
      if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar-4') ) : ?>
<?php endif; ?>
</div>


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
		      mobileBanner1.load('<?php bloginfo('url'); ?> #extra-1', function() {
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
		      sidebarBanner1.load('<?php bloginfo('url'); ?> #extra-1', function() {
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

