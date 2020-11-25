<?php

namespace Wulff\util;

class RepoUtil
{
    const COUNT = 10;

    public static function getOffset($page)
    {
        return self::COUNT * $page;
    }

}