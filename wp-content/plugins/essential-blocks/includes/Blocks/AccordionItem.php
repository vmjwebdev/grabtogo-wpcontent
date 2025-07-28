<?php
namespace EssentialBlocks\Blocks;

use EssentialBlocks\Core\Block;

class AccordionItem extends Block
{
    protected $frontend_styles = [ 'essential-blocks-fontawesome' ];
    /**
     * Unique name of the block.
     *
     * @return string
     */
    public function get_name()
    {
        return 'accordion-item';
    }
}
