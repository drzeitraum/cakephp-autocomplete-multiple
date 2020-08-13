<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

/**
 * Base helper
 */
class BaseHelper extends Helper
{
    /**
     * Default configuration.
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * illumination method - highlighting the search phrase
     */
    public function illumination($search = null, $name = null)
    {
        $pattern = "/({$search})/iu";
        $replacement = "<b>$1</b>";
        return preg_replace($pattern, $replacement, $name);
    }

}
