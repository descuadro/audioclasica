<?php
/**
 * Main Template File
 *
 * This file is used to display a page when nothing more specific matches a query.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package themeHandle
 */

$post_classes = array(
	'story',
);

get_header(); ?>

<section class="cat-feed feed text-based">

	<?php if ( have_posts() ) : ?>

		<?php /* Start the Loop */ ?>

		<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

			<div class="image">
				<?php if ( has_post_thumbnail() ) { the_post_thumbnail(); } ?>
    	</div>

			<header class="entry-header">
				<h1 class="entry-title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h1>
				<h6 class="entry-details">
					<span class="entry-author"><?php the_author() ?></span>
					<?php the_category('/ ') ?>
				</h6>
			</header><!-- .entry-header -->

		</article><!-- #post-<?php the_ID(); ?> -->
	<?php endwhile; // end of the loop. ?>

	<?php else : ?>

		<?php get_template_part( 'content', 'none' ); ?>

	<?php endif; ?>

</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>