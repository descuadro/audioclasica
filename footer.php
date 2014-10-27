<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package themeHandle
 */
?>

	</div><!-- #main -->

</div><!-- #page -->
<footer id="colophon" role="contentinfo">
	<div id="copyright">
		&copy; <?php echo date( 'Y' ); echo '&nbsp;'; echo bloginfo( 'name' ); ?><br>
		Site by <a href="designerURI" target="_blank" rel="nofollow">themeDesigner</a> &amp;
		<a href="authorURI" target="_blank" rel="nofollow">themeAuthor</a>
	</div>
</footer><!-- #colophon -->

<?php wp_footer(); ?>

<!--Audioclasik scripts -->

 <script src="/scripts/jquery.min.js"></script>

<script>window.jQuery || document.write('<script src="/_bower_components/jquery.js"><\/script>')</script>

<!-- build:js(app) /scripts/script.js -->
<script src="/scripts/marka.js"></script>
<script src="/scripts/main.js"></script>
<script src=//use.typekit.net/hxo6ukw.js></script><script>try{Typekit.load();}catch(e){}</script>
<!-- endbuild -->

</body>
</html>