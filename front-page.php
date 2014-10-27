<?php
/**
 * Home Page
 *
 * @package themeHandle
 */

get_header(); ?>

main role="main" class="home">

	<!-- Hola Negro! -->
	<!-- Cómo va? -->
	<!-- Todo bien gracias -->
	<!-- No tan bien, verás -->
	<!-- Por? Qué onda? -->
	<!-- No se, te veo desmejorado -->
	<!-- Se me está cayendo el pelo... -->
	<!-- Ya veo. Te llaman Samuel L. Jackson -->

<section class="home-feed feed">
	<?php while ( have_posts() ) : the_post(); ?>
		<article class="story story-1 tendencias">
		    <header>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header><!-- .entry-header -->
		
					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'themeTextDomain' ) . '</span>', 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->
				</article><!-- #post-<?php the_ID(); ?> -->
			</header>
		</article>

	<style>
		.feed .story-1 { background: url('images/story1.fb6a.jpg'); }
	</style>
	<?php endwhile; // end of the loop. ?>
</section>

<!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>