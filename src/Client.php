<?php

namespace Olsgreen\AbstractApi;

use Closure;

interface Client
{
    public function getHttp();
    public function preflight(Closure $callback): Client;
}