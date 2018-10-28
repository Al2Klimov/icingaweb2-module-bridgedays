<?php

namespace Icinga\Module\Bridgedays\Intrface;

use Icinga\Module\Bridgedays\Forms\ImportForm;

interface Input
{
    public function getId();

    public function getName();

    public function getFields();

    public function import(ImportForm $form);
}
