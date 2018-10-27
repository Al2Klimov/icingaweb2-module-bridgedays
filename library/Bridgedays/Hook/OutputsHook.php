<?php

namespace Icinga\Module\Bridgedays\Hook;

abstract class OutputsHook
{
    final public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
    }

    abstract public function getOutputs();
}
