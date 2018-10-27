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
                            $inputs[$idPrefix . $input->getId()] = [$input->getName(), $input->getFields()];
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
                            $outputs[$idPrefix . $output->getId()] = [$output->getName(), $output->getFields()];
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
        ]);

        $outputs = [];

        foreach ($this->getOutputs() as $id => list($name, $_)) {
            $outputs[$id] = $name;
        }

        if (count($outputs) != 1) {
            $outputs[''] = '';
        }

        asort($outputs);

        $this->addElement('select', 'output', [
            'label'        => $this->translate('Output'),
            'description'  => $this->translate('Holiday request registry'),
            'required'     => true,
            'multiOptions' => $outputs,
        ]);

        $this->setSubmitLabel($this->translate('Select'));
    }
}
