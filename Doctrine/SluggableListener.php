<?php
/*
 * This file is part of CWD CommonBundle.
 *
 * (c)2018 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Doctrine;

use Gedmo\Sluggable\SluggableListener as BaseSluggableListener;

/**
 * Class SluggableListener
 * @package Cwd\CommonBundle\Doctrine
 */
class SluggableListener extends BaseSluggableListener
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setTransliterator(array(UmlautTransliterator::class, 'transliterate'));
    }
}
