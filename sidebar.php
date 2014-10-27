<?php
/**
 * The main sidebar
 *
 * @package themeHandle
 */

?>
<div id="secondary" class="widget-area" role="complementary">
	<?php dynamic_sidebar('sidebar-1'); ?>
</div>
<div id="widgetized-area">

	<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar-1')) : else : ?>

	<div class="pre-widget">
		<p><strong>Banner 1</strong></p>
		<p>img</p>
	</div>

	<?php endif; ?>

</div>

<!-- #secondary .widget-area -->

