<?php
namespace EssentialBlocks\Blocks;

use EssentialBlocks\Core\Block;

class ProgressBar extends Block
{
    protected $frontend_scripts = [ 'essential-blocks-progress-bar-frontend' ];
    /**
     * Unique name of the block.
     *
     * @return string
     */
    public function get_name()
    {
        return 'progress-bar';
    }

    /**
     * Register all other scripts
     *
     * @return void
     */
    public function register_scripts()
    {
        $this->assets_manager->register(
            'progress-bar-frontend',
            $this->path() . '/frontend.js',
        );
    }
}
