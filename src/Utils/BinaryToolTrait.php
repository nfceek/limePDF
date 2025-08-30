<?php

namespace LimePDF\Util;

trait BinaryToolsTrait
{
    protected function _getUSHORT(string $data, int $pos): int
    {
        return (ord($data[$pos]) << 8) | ord($data[$pos + 1]);
    }
}
