<?php
class De_Snippet {
	public $owner;
	public $in_the_snippet = false;
	
	public $snippet_default;
	
	public function __construct() {
	}

	public function snippet( De_Item $owner, $snippet ) {
		global $post;
		global $direct_image;
		global $direct_list_item;
		
		$content = '';
		
		if ( file_exists ( get_stylesheet_directory() . '/snippets/' . $snippet . '-edit.php' ) && De_Store::is_editable( $owner ) ) {
			$filename = get_stylesheet_directory() . '/snippets/' . $snippet . '-edit.php';
		} elseif ( file_exists ( get_stylesheet_directory() . '/snippets/' . $snippet . '-view.php' ) && ! De_Store::is_editable( $owner ) ) {
			$filename = get_stylesheet_directory() . '/snippets/' . $snippet . '-view.php';
		} elseif ( file_exists ( get_stylesheet_directory() . '/snippets/' . $snippet . '.php' ) ) {
			$filename = get_stylesheet_directory() . '/snippets/' . $snippet . '.php';
		} elseif ( ! empty( $this->snippet_default ) ) {
			$filename = $this->snippet_default;
		}

		if ( isset( $filename ) ) {
			$this->owner = $owner;
			$this->in_the_snippet = true;
			
			ob_start();
			
			include( $filename );
			
			$content = ob_get_contents();
			ob_end_clean();
			
			$this->in_the_snippet = false;
			$this->owner = null;
		}
		
		return $content;
	}
}
