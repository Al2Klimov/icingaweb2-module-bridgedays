<?php

namespace Icinga\Module\Bridgedays\Controllers;

use Icinga\Module\Bridgedays\Forms\ImportForm;
use Icinga\Web\Controller;
use Icinga\Web\Url;
use Icinga\Web\Widget\Tabs;

class IndexController extends Controller
{
    protected $requiresAuthentication = false;

    public function indexAction()
    {
        $this->view->form = $form = new ImportForm();
        $form->handleRequest();

        $this->view->tabs = (new Tabs)->add('import-bridge-days', [
            'label'     => $this->translate('Import bridge days'),
            'title'     => $this->translate('Import bridge days'),
            'icon'      => 'download',
            'url'       => Url::fromRequest(),
            'active'    => true
        ]);
    }
}
