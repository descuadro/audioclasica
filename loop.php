	<div class="empty" style="display: inline;"></div>
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

					<div class="body">
						<?php the_content(); ?>
						<p align="right"><a href="<?php the_permalink() ?>"><strong>más <small><small>>></small></small></strong></a></p>
					</div>
				</header>

			</article>
	<?php endwhile; // end of the loop. ?>