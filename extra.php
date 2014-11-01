<?php
/**
 * The template for displaying Banners.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to themeFunction_comment() which is
 * located in the functions.php file.
 *
 * @package themeHandle
 */
?>

<?php
      if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('sidebar-2') ) : ?>
<?php endif; ?>

<div id="extra-1">
	<a href="#" class="extra">
        <picture>
            <source srcset="http://lorempixel.com/350/332/abstract/" media="(min-width: 1024px)">
            <source srcset="http://lorempixel.com/1024/600/abstract/" media="(min-width: 600px)">
            <img srcset="http://lorempixel.com/600/570/abstract/">
        </picture>
	</a>
</div>

<div id="extra-2">
	<a href="#" class="extra">
        <picture>
            <source srcset="http://lorempixel.com/350/450/abstract/" media="(min-width: 1024px)">
            <source srcset="http://lorempixel.com/1024/768/abstract/" media="(min-width: 600px)">
            <img srcset="http://lorempixel.com/600/770/abstract/">
        </picture>
	</a>
</div>

<div id="extra-3">
	<a href="#" class="extra">
        <picture>
            <source srcset="http://lorempixel.com/350/526/abstract/" media="(min-width: 1024px)">
            <source srcset="http://lorempixel.com/1024/768/abstract/" media="(min-width: 600px)">
            <img srcset="http://lorempixel.com/600/900/abstract/">
        </picture>
	</a>
</div>