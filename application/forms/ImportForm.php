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
    protected $step = 0;

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

    public function getStep()
    {
        return $this->step;
    }

    public function init()
    {
        $this->setName('form_import');
    }

    public function createElements(array $formData)
    {
        if (isset($formData['bridgedays'])) {
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
                'autosubmit'   => true
            ]);

            if (isset($formData['output'])) {
                $output = $formData['output'];
            } else {
                foreach ($outputs as $output => $_) {
                    break;
                }
            }

            $outputs = $this->getOutputs();

            if (isset($outputs[$output])) {
                $this->addElements($outputs[$output][1]->getFields());
            }

            foreach (str_split($formData['bridgedays'], 22) as $bridgeday) {
                $matches = [];

                if (preg_match('/\AX(\d{4})X(\d{2})X(\d{2})X(\d{4})X(\d{2})X(\d{2})\z/', $bridgeday, $matches)) {
                    list($_, $y1, $m1, $d1, $y2, $m2, $d2) = $matches;

                    $this->addElement('checkbox', "X{$y1}X{$m1}X$d1", [
                        'label'        => sprintf($this->translate('%s to %s'), "$y1-$m1-$d1", "$y2-$m2-$d2"),
                        'description'  => sprintf($this->translate('Request holidays from %s to %s'), "$y1-$m1-$d1", "$y2-$m2-$d2"),
                        'value'        => 1
                    ]);
                }
            }

            $this->addElement('hidden', 'bridgedays');

            $this->setSubmitLabel($this->translate('Request'));

            $this->step = 1;
        } else {
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
                $this->addElements($inputs[$input][1]->getFields());
            }

            $this->setSubmitLabel($this->translate('Import'));
        }
    }

    public function onSuccess()
    {
        switch ($this->step) {
            case 0:
                $input = $this->getInputs()[$this->getValue('input')][1];
                $bridgedays = $input->import($this);

                if (empty($bridgedays)) {
                    $this->addError($this->translate('No bridge days found'));
                    return false;
                }

                $sandbox = new Form;
                $preserve = [];

                foreach ($sandbox->getElements() as $element) {
                    $preserve[$element->getName()] = null;
                }

                $sandbox->addElements($input->getFields());

                foreach ($sandbox->getElements() as $element) {
                    $name = $element->getName();

                    if (!array_key_exists($name, $preserve)) {
                        $this->removeElement($name);
                    }
                }

                $this->removeElement('input');
                $this->removeElement('start');
                $this->removeElement('end');
                $this->removeElement('maxdays');
                $this->removeElement('btn_submit');

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
                    'autosubmit'   => true
                ]);

                foreach ($outputs as $output => $_) {
                    break;
                }

                $outputs = $this->getOutputs();

                if (isset($outputs[$output])) {
                    $this->addElements($outputs[$output][1]->getFields());
                }

                $checkboxes = '';

                foreach ($bridgedays as $from => $to) {
                    $name = str_replace('-', 'X', "-$from-$to");

                    $this->addElement('checkbox', $name, [
                        'label'        => sprintf($this->translate('%s to %s'), $from, $to),
                        'description'  => sprintf($this->translate('Request holidays from %s to %s'), $from, $to),
                        'value'        => 1
                    ]);

                    $checkboxes .= $name;
                }

                $this->addElement('hidden', 'bridgedays', ['value' => $checkboxes]);

                $this->setSubmitLabel($this->translate('Request'));
                $this->addSubmitButton();

                $this->step = 1;

                return false;
            case 1:
                $bridgedays = [];

                foreach (str_split($this->getValue('bridgedays'), 22) as $bridgeday) {
                    $matches = [];

                    if (preg_match('/\AX(\d{4})X(\d{2})X(\d{2})X(\d{4})X(\d{2})X(\d{2})\z/', $bridgeday, $matches)) {
                        list($_, $y1, $m1, $d1, $y2, $m2, $d2) = $matches;

                        $bridgedays["$y1-$m1-$d1"] = "$y2-$m2-$d2";
                    }
                }

                $selected = [];

                foreach ($this->getElements() as $element) {
                    $matches = [];

                    if (preg_match('/\AX(\d{4})X(\d{2})X(\d{2})\z/', $element->getName(), $matches) && $element->isChecked()) {
                        list($_, $y1, $m1, $d1) = $matches;
                        $selected["$y1-$m1-$d1"] = $bridgedays["$y1-$m1-$d1"];
                    }
                }

                if (empty($selected)) {
                    $this->addError($this->translate('No bridge days selected (if you HAVE selected some, please just try again)'));
                    return false;
                }

                $this->getOutputs()[$this->getValue('output')][1]->export($this, $selected);
                return true;
        }
    }
}
