<?php
namespace EssentialBlocks\Blocks;

use EssentialBlocks\Core\Block;

class InteractivePromo extends Block {
	protected $frontend_styles = array( 'essential-blocks-hover-effects-style' );

	/**
	 * Unique name of the block.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'interactive-promo';
	}
}
