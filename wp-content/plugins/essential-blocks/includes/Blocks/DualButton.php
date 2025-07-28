<?php
namespace EssentialBlocks\Blocks;

use EssentialBlocks\Core\Block;

class DualButton extends Block
{
    /**
     * Unique name of the block.
     *
     * @return string
     */
    public function get_name()
    {
        return 'dual-button';
    }

    /**
     * Render callback
     */
    public function render_callback( $attributes, $content )
    {
        if ( ! is_admin() && isset( $attributes[ 'connectorType' ] ) && $attributes[ 'connectorType' ] === 'icon' ) {
            $this->assets_manager->enqueue(
                'fontawesome-frontend',
                'css/font-awesome5.css'
            );
        }

        return $content;
    }
}
