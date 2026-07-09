<?php

namespace App\Modules\Core\Application\Actions;

abstract class BaseAction
{
    abstract public function handle(mixed ...$arguments): mixed;
}
