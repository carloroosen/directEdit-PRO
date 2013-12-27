<!DOCTYPE HTML>
<html>
<head>
<meta charset="UTF-8" />
<meta name="robots" content="index, follow" />
<meta name="description" content="<?php direct_bloginfo( 'description' ); ?>" />
<meta name="keywords" content="" />
<title><?php direct_bloginfo( 'title' ); ?></title>
<?php
	wp_head();
?>

</head>

<body>
<header role="banner" id="banner">
	<nav role="navigation" id="primary-nav">
		<?php direct_menu( array( 'theme_location' => 'direct_main', 'depth' => 1 ) ); ?>
	</nav>
</header>

<section id="main">
