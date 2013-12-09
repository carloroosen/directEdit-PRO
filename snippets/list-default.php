<div>
	<ul>
		<?php while ( direct_list_have_items() ) : direct_list_the_item(); ?>
		<li>
			<?php direct_editable( 'link', 'postmeta', array( 'key' => 'de_link', 'postId' => $direct_list_item->ID, 'options' => 'link' ) ); ?>
				<?php direct_editable( 'text', 'postmeta', array( 'options' => 'title', 'postId' => $direct_list_item->ID, 'key' => 'de_text', 'container' => 'p' ) ); ?>
			</a>
		</li>
		<?php endwhile; ?>
	</ul>
</div>
