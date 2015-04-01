<?php
class De_Snippet_List extends De_Snippet {
	public $in_the_loop = false;
	
	public $item_type;
	public $current_item;
	public $items;
	public $item;
	public $item_count;

	private $list_stack = array();
	
	public function __construct() {
		// Default list snippet
		$this->snippet_default = DIRECT_PATH . 'pro/snippets/list-default.php';
		
		parent::__construct();
	}
	
	public function snippet( De_Item $owner, $snippet, $list = null ) {
		// Nested snippets implementation
		if ( $this->in_the_snippet ) {
			$ls =  array( 'owner' => $this->owner, 'item_type' => $this->item_type, 'current_item' => $this->current_item, 'items' => $this->items );
			array_push( $this->list_stack, $ls );
		}
		
		if ( $owner->get_setting( 'itemType' ) ) {
			$this->item_type = $owner->get_setting( 'itemType' );
		} else {
			$this->item_type = 'de_list_item';
		}
		$this->current_item = -1;
		if ( empty( $list ) ) {
			$list = $owner->list;
		}
		$this->items = array();
		if ( $list ) {
			foreach( $list as $i ) {
				$item = get_post( $i );
				if ( is_object( $item ) )
					$this->items[] = $item;
			}
		}
		$this->item = null;
		$this->item_count = count( $this->items );

		$content = parent::snippet( $owner, $snippet );
		
		if ( count( $this->list_stack ) ) {
			$ls = array_pop( $this->list_stack );
			$this->owner = $ls[ 'owner' ];
			$this->item_type = $ls[ 'item_type' ];
			$this->current_item = $ls[ 'current_item' ];
			$this->items = $ls[ 'items' ];
			$this->item = $this->items[ $this->current_item ];
			$this->item_count = count( $this->items );
			
			$this->in_the_snippet = true;
		}
		
		return $content;
	}

	public function have_items() {
		if ( $this->current_item + 1 < $this->item_count ) {
			return true;
		} else {
			$this->in_the_loop = false;
			return false;
		}
	}

	public function the_item() {
		global $direct_list_item;
		
		// Loop has just started
		if ( $this->current_item == -1 ) {
			$this->in_the_loop = true;
		}

		$direct_list_item = $this->next_item();
	}

	public function rewind_items() {
		global $direct_list_item;
		
		$this->current_item = -1;
		$this->item = null;
		
		$direct_list_item = null;
	}

	public function next_item() {
		$this->current_item ++;

		$this->item = $this->items[ $this->current_item ];
		return $this->item;
	}
}
