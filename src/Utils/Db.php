<?php

namespace TheSystem\Utils;

use mysqli;

class Db
{
    public function connect(): mysqli
    {
        $host = 'localhost';
        $user = 'thesystemuser';
        $password = 'thesystempsw';
        $dbname = 'thesystem';

        return new mysqli($host, $user, $password, $dbname);
    }
}
