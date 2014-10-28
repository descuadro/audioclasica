<?php
/**
 * The main sidebar
 *
 * @package themeHandle
 */

?>
<div class="head-wrapper">
	<nav role="navigation" id="nav">
		<header>
			<h1 class="site-logo">Audioclásica</h1>
			<button type="button" role="button" aria-label="Toggle Navigation" class="transformicon navicon">
			</button>
		</header>

		<ul id="categories">
			<?php $args = array(
				'type'                     => 'post',
				'child_of'                 => 0,
				'parent'                   => '',
				'orderby'                  => 'name',
				'order'                    => 'ASC',
				'hide_empty'               => 1,
				'hierarchical'             => 1,
				'exclude'                  => '',
				'include'                  => '',
				'number'                   => '',
				'taxonomy'                 => 'category',
				'title_li'    				=> __(''),
				'pad_counts'               => false 
			); ?>
			<?php wp_list_categories( $args ); ?> 
		</ul>

		<div class="pages">
			<?php $args = array(
			'authors'      => '',
			'child_of'     => 0,
			'date_format'  => get_option('date_format'),
			'depth'        => 0,
			'echo'         => 1,
			'exclude'      => '',
			'include'      => '',
			'link_after'   => '',
			'link_before'  => '',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'show_date'    => '',
			'sort_column'  => 'menu_order, post_title',
		        'sort_order'   => '',
			'title_li'     => __(''), 
			'walker'       => ''
			); ?>
			<?php wp_list_pages( $args ); ?>
		</div>

		<ul class="links">

			<?php get_links('-1', '<li><span>', '</span></li>', '', FALSE, 'id', FALSE,
				FALSE, -1, TRUE, TRUE); ?>
		</ul>
	</nav>
</div>
tester
<!-- #secondary .widget-area -->

