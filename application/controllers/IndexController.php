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

        switch ($form->getStep()) {
            case 0:
                $this->mkTabs('import-bridge-days', 'download', 'Import bridge days', 'Import bridge days');
                break;
            case 1:
                $this->mkTabs('request-holidays', 'upload', 'Request holidays', 'Request holidays');
                break;
        }
    }

    protected function mkTabs($id, $icon, $label, $title)
    {
        $this->view->tabs = (new Tabs)->add($id, [
            'label'  => $label,
            'title'  => $title,
            'icon'   => $icon,
            'url'    => Url::fromRequest(),
            'active' => true
        ]);
    }
}
