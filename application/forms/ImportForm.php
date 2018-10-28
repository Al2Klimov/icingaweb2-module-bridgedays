<?php

namespace Icinga\Module\Bridgedays\Forms;

use Icinga\Application\Hook;
use Icinga\Module\Bridgedays\Hook\InputsHook;
use Icinga\Module\Bridgedays\Hook\OutputsHook;
use Icinga\Module\Bridgedays\Intrface\Input;
use Icinga\Module\Bridgedays\Intrface\Output;
use Icinga\Web\Form;

class ImportForm extends Form
{
    protected $inputs = null;
    protected $outputs = null;

    protected function getInputs()
    {
        if ($this->inputs === null) {
            $inputs = [];

            foreach (Hook::all('Bridgedays\Inputs') as $hook) {
                if ($hook instanceof InputsHook) {
                    $idPrefix = md5(get_class($hook)) . 'X';

                    foreach ($hook->getInputs() as $input) {
                        if ($input instanceof Input) {
                            $inputs[$idPrefix . $input->getId()] = [$input->getName(), $input];
                        }
                    }
                }
            }

            $this->inputs = $inputs;
        }

        return $this->inputs;
    }

    protected function getOutputs()
    {
        if ($this->outputs === null) {
            $outputs = [];

            foreach (Hook::all('Bridgedays\Outputs') as $hook) {
                if ($hook instanceof OutputsHook) {
                    $idPrefix = md5(get_class($hook)) . 'X';

                    foreach ($hook->getOutputs() as $output) {
                        if ($output instanceof Output) {
                            $outputs[$idPrefix . $output->getId()] = [$output->getName(), $output];
                        }
                    }
                }
            }

            $this->outputs = $outputs;
        }

        return $this->outputs;
    }

    public function init()
    {
        $this->setName('form_import');
    }

    public function createElements(array $formData)
    {
        $inputs = [];

        foreach ($this->getInputs() as $id => list($name, $_)) {
            $inputs[$id] = $name;
        }

        if (count($inputs) != 1) {
            $inputs[''] = '';
        }
        
        asort($inputs);
        
        $this->addElement('select', 'input', [
            'label'        => $this->translate('Input'),
            'description'  => $this->translate('Bridge days data source'),
            'required'     => true,
            'multiOptions' => $inputs,
            'autosubmit'   => true
        ]);

        $this->addElement('dateTimePicker', 'start', [
            'label'        => $this->translate('Start date'),
            'description'  => $this->translate('Query only bridge days not before ...'),
            'required'     => true,
        ]);

        $this->addElement('dateTimePicker', 'end', [
            'label'        => $this->translate('End date'),
            'description'  => $this->translate('Query only bridge days not after ...'),
            'required'     => true,
        ]);

        $this->addElement('number', 'maxdays', [
            'label'        => $this->translate('Bridge days'),
            'description'  => $this->translate('Request holidays only for periods not longer than ... days'),
            'required'     => true,
            'min'          => 0,
            'value'        => 1,
        ]);

        if (isset($formData['input'])) {
            $input = $formData['input'];
        } else {
            foreach ($inputs as $input => $_) {
                break;
            }
        }

        $inputs = $this->getInputs();

        if (isset($inputs[$input])) {
            foreach ($inputs[$input][1]->getFields() as $field) {
                $this->addElements([$field]);
            }
        }

        $this->setSubmitLabel($this->translate('Import'));
    }
}
