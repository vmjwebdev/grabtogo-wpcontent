<?php
namespace EssentialBlocks\Blocks;

use EssentialBlocks\Core\Block;

class FlipBox extends Block
{
    protected $frontend_styles = [ 'essential-blocks-fontawesome' ];

    protected $frontend_scripts = [ 'essential-blocks-flipbox-frontend' ];

    /**
     * Register all other scripts
     * @return void
     */
    public function register_scripts()
    {
        $this->assets_manager->register(
            'flipbox-frontend',
            $this->path() . '/frontend.js'
        );
    }

    /**
     * Unique name of the block.
     *
     * @return string
     */
    public function get_name()
    {
        return 'flipbox';
    }
}
