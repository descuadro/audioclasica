<div class="wrap about-wrap">
	<h1><?php printf( __( 'Welcome to Headway %s', 'headway' ), '3.7' ); ?></h1>

	<div class="about-text"><?php printf( __( 'Thank you for updating!  Headway 3.7 has many improvements we think you\'ll enjoy.', 'headway' ) ); ?></div>

	<hr />

	<div class="changelog customize">
		<div class="feature-section col two-col">
			<div>
				<img src="//gallery.mailchimp.com/b46cd46ad87f36a99ca3c657f/images/Screen_Shot_2014_04_29_at_1.17.51_PM.png">

				<h4>Fresh User Interface</h4>

				<p>We fell in love with the WordPress 3.8 user interface as soon as we saw it. Headway's dark interface was
				quickly aging and fell short in terms of readability and contrast. It also didn't feel like part of the
				WordPress experience.</p>

				<p>Not only did the Visual Editor get a new paint job, but we have also made dragging, animations, and
				general usage feel much more snappy and responsive.</p>
			</div>
			<div class="last-feature">
				<img src="//gallery.mailchimp.com/b46cd46ad87f36a99ca3c657f/images/Screen_Shot_2014_04_30_at_9.29.59_AM.png">

				<h4>Headway Templates Moved to WordPress Admin</h4>

				<p>To reduce confusion between Headway Templates and Layout Templates we have moved the Templates panel
					from the Visual Editor to the WordPress Admin.</p>
			</div>
		</div>
	</div>

	<div class="changelog customize">
		<div class="feature-section col two-col">
			<div>
				<!--				<img src="//s.w.org/images/core/3.9/theme.jpg?0">-->

				<h4>Improved Data Handling</h4>

				<p>Headway 3.7's blocks, wrappers, design settings, and layout meta are now stored in custom MySQL tables.
				We have carefully planned out the data structure to ensure that your work will be more reliably stored.</p>

				<p>We've also taken steps to cache all Headway data from the MySQL tables with the WordPress Transient and
				Caching APIs to your Headway site stays speedy.</p>
			</div>
			<div class="last-feature">
				<!--				<img src="//s.w.org/images/core/3.9/theme.jpg?0">-->

				<h4>Snapshots</h4>

				<p>Let's face it—losing work is a major waste of time and money! Fortunately, we have added Snapshots in
					Headway 3.7.</p>

				<p>Snapshots are a simple backup system for Headway that allows you to rollback to any snapshot and
					restore all blocks, wrappers, layout settings, as well as design settings at any time.</p>

				<p>Headway will also automatically store snapshots when you save in the Visual Editor.</p>
			</div>
		</div>
	</div>

	<hr />

	<div class="changelog under-the-hood">
		<h3>Additional Changes and Improvements</h3>

		<div class="feature-section col three-col">
			<div>
				<h4>Improved Live CSS Editor</h4>

				<p>Live CSS Editor Headway's Live CSS Editor is now powered by Ace. Ace has fantastic code-collapsing
					capabilities, syntax highlighting, as well as suggestions for your CSS.</p>

				<p>We've also changed the Live CSS editor to open in a new browser window rather than a box in the
				Visual Editor. This means that you can easily tab between two windows or put the Live CSS editor on
				another monitor if you use multiple displays.</p>
			</div>
			<div>
				<h4>New Block: Search</h4>

				<p>Display a simple search form with a block</p>

				<h4>New Block: Pin Board</h4>

				<p>Pin Board has been moved into the core of Headway and includes many new improvements regarding
					category queries, taxonomies, and more!</p>
			</div>
			<div class="last-feature">
				<h4>Visual Editor Speed Improvements</h4>

				<p>The Visual Editor now uses Require.js to load scripts.  This improves speed and makes debugging easier.</p>

				<h4>Basic Undo/Redo (Experimental)</h4>

				<p>Implemented basic Undo/Redo functionality. Delete a block on accident? Simply push Cmd + Z or Ctrl +
					Z and the change will be reverted instantly.</p>
			</div>
		</div>

		<hr>

		<div class="return-to-dashboard">
			<a href="<?php echo admin_url('admin.php?page=headway-visual-editor'); ?>">Go to Headway Visual Editor</a>
		</div>

	</div>
</div>