<?php

namespace Wulff\util;


class HttpCode
{
    // success
    const OK = 200;
    const CREATED = 201;
    const NO_CONTENT = 204;

    // client errors
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const NOT_ACCEPTABLE = 406;
    const CONFLICT = 409;

    // server errors
    const INTERNAL_SERVER_ERROR = 500;
    const BAD_GATEWAY = 502;

}