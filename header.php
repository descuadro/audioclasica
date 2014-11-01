<?php
/**
 * The header template
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package themeHandle
 */
?>

<!DOCTYPE html>

<!--[if lt IE 9]>
<html id="ie" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?> class="text-based">
<!--<![endif]-->

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php wp_title( '|', true, 'right' ); ?></title>

    <!-- favicon & links -->
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" type="image/x-icon">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

    <!-- all other scripts are enqueued via functions.php -->
    <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/vendor/html5shiv.js" type="text/javascript"></script>
    <![endif]-->

    <?php // Lets other plugins and files tie into our theme's <head>:
    wp_head(); ?>

    <!-- <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet"> -->

    <!-- Typekit -->
    <script src="//use.typekit.net/hxo6ukw.js"></script>
    <script>try{Typekit.load();}catch(e){}</script>
</head>

<body <?php body_class(); ?>>

    <div class="page-container">
        <header role="banner">
            <div class="logo-holder">
                <h1 class="site-logo">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        Audioclásica
                        <?php if (is_category()): ?> - <span class="cat-title"><?php single_cat_title( '', true ); ?></span><?php endif ?>
                    </a>
                </h1>
            </div>

            <nav class="toggle-nav">
                <button type="button" role="button" aria-label="Toggle Navigation" class="transformicon navicon entypo-menu"></button>
                <button type="button" role="button" aria-label="Toggle Sorting" class="transformicon sorticon entypo-dot-3"></button>
            </nav>
        </header>

        <div class="head-wrapper">

            <!-- includes -->
            <?php get_sidebar(); ?>
        </div>

        <main role="main" id="main">