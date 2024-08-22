<?php

namespace App\Models\Contracts;

interface TraceableContract
{
    public function traces();

    public function links();
}
