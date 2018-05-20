<?php
/*
 * This file is part of CWD Generic Bundle.
 *
 * (c)2015 Ludwig Ruderstaller <lr@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Doctrine;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * Class UmlautTransliterator
 * @package Cwd\CommonBundle\Doctrine
 */
class UmlautTransliterator
{
    /**
     * @param string $text
     * @param string $separator
     *
     * @return mixed
     */
    public static function transliterate($text, $separator = '-')
    {
        $text = Urlizer::unaccent($text);

        return Urlizer::urlize($text, $separator);
    }
}
