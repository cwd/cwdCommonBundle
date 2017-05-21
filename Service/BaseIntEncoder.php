<?php
/*
 * This file is part of MailingOwl
 *
 * (c)2017 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cwd\CommonBundle\Service;

/**
 * Class BaseIntEncoder.
 *
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
class BaseIntEncoder
{
    //const $CODESET = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //readable character set excluded (0,O,1,l)
    //const CODESET = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
    const CODESET = '23456789abcdefghijkmnopqrstuvwxyz';

    /**
     * Entcode given Integer.
     *
     * @param int $n
     *
     * @return string
     */
    public static function encode($n)
    {
        $base = strlen(self::CODESET);
        $converted = '';

        while ($n > 0) {
            $converted = substr(self::CODESET, bcmod($n, $base), 1).$converted;
            $n = self::bcFloor(bcdiv($n, $base));
        }

        return $converted;
    }

    /**
     * Decode given Integer.
     *
     * @param string $code
     *
     * @return int
     */
    public static function decode($code)
    {
        $base = strlen(self::CODESET);
        $c = '0';
        for ($i = strlen($code); $i; --$i) {
            $c = bcadd($c, bcmul(strpos(self::CODESET, substr($code, (-1 * ($i - strlen($code))), 1)), bcpow($base, $i - 1)));
        }

        return bcmul($c, 1, 0);
    }

    private static function bcFloor($x)
    {
        return bcmul($x, '1', 0);
    }

    private static function bcCeil($x)
    {
        $floor = bcFloor($x);

        return bcadd($floor, ceil(bcsub($x, $floor)));
    }

    private static function bcRound($x)
    {
        $floor = bcFloor($x);

        return bcadd($floor, round(bcsub($x, $floor)));
    }
}

