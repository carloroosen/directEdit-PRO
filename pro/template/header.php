<!DOCTYPE HTML>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php wp_title(); ?></title>
<?php wp_head(); ?>

</head>

<body>
<header role="banner" id="banner">
	<nav role="navigation" id="primary-nav">
		<?php direct_menu( array( 'theme_location' => 'direct_main', 'depth' => 1 ) ); ?>
	</nav>
</header>

<section id="main">
