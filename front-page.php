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

<section class="home-feed feed text-based">

	<?php /* Start the Loop */ ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

			<div class="image">
				<?php if ( has_post_thumbnail() ) { the_post_thumbnail(); } ?>
    	</div>

			<header class="entry-header">
				<h1 class="story-title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
				<h6 class="story-details">
					<span class="story-author"><?php the_author() ?></span>
					<?php the_category('/ ') ?>
				</h6>
			</header><!-- .entry-header -->

		</article><!-- #post-<?php the_ID(); ?> -->
	<?php endwhile; // end of the loop. ?>

</section>

<!-- #primary -->
<?php get_footer(); ?>