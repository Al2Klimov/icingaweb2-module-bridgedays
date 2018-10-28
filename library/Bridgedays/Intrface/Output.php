<?php

namespace Icinga\Module\Bridgedays\Intrface;

use Icinga\Module\Bridgedays\Forms\ImportForm;

interface Output
{
    public function getId();

    public function getName();

    public function getFields();

    public function export(ImportForm $form, array $bridgedays);
}
