<?php
/**
 * Home Page
 *
 * @package themeHandle
 */

$post_classes = array(
	'story',
);

get_header(); ?>

<main role="main" class="home">

<section class="home-feed feed">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?> class="story tendencias">
		    <header>
					<header class="entry-header">
						<h1 class="entry-title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
						<h6 class="entry-details">
							<span class="entry-author"><?php the_author() ?></span>
							<a href="#" class="category"><?php the_category('/ ') ?></a>
						</h6>
					</header><!-- .entry-header -->
				</article>
			</header>
		</article><!-- #post-<?php the_ID(); ?> -->
	<?php endwhile; // end of the loop. ?>
</section>

<!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>