<?php
/**
 * Home Page
 *
 * @package themeHandle
 */

$post_classes = array( 'story', );
get_header(); ?>

<section class="home-feed feed">

	<?php /* Start the Loop */ ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php
			if ( has_post_thumbnail() ) {
					$post_image_id = get_post_thumbnail_id($post->ID);

					if ($post_image_id) {
						$thumbnail = wp_get_attachment_image_src( $post_image_id, 'post-thumbnail', false);
						if ($thumbnail) (string)$thumbnail = $thumbnail[0];
					}
    	}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

				<style> #post-<?php the_ID(); ?> { background-image: url('<?php echo $thumbnail; ?>'); } </style>

				<?php if (  has_post_thumbnail() ): ?>
					<div class="image"><?php the_post_thumbnail(); ?></div>
				<?php endif ?>

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