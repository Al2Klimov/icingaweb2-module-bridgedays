<?php

namespace Icinga\Module\Bridgedays\Hook;

abstract class InputsHook
{
    final public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
    }

    abstract public function getInputs();
}
