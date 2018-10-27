<?php

namespace Icinga\Module\Bridgedays\Intrface;

interface Output
{
    public function getId();

    public function getName();

    public function getFields();
}
