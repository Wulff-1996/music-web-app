<?php

namespace Wulff\util;

class RepoUtil
{
    const COUNT = 10;

    public static function getOffset(int $page, ?int $count = null)
    {
        $count = ($count != null) ? $count : self::COUNT;
        return $count * $page;
    }

}