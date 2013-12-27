<?php
class De_Snippet_Image extends De_Snippet {
	public $mode = 'public';
	
	public function snippet( De_Item_Image $owner, $snippet ) {
		// No nested snippets
		if ( $this->in_the_snippet ) {
			return '';
		}
		
		return parent::snippet( $owner, $snippet );
	}
}
