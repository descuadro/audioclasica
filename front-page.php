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
         $thumbnail = get_the_post_thumbnail($post->ID,'thumbnail');
    	}
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>

			<?php if ( $image_based ): ?>
				<style> #post-<?php the_ID(); ?> { background-image: url('<?php echo $thumbnail; ?>'); } </style>

			<?php else: ?>

				<?php if (  has_post_thumbnail() ): ?>
					<div class="image"><?php the_post_thumbnail(); ?></div>
				<?php endif ?>

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