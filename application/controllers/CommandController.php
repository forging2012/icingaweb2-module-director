<?php

namespace Icinga\Module\Director\Controllers;

use Icinga\Module\Director\Web\Controller\ObjectController;
use Icinga\Data\Filter\Filter;

class CommandController extends ObjectController
{
    public function init()
    {
        parent::init();
        if ($this->object && ! $this->object->isExternal()) {
            $this->getTabs()->add('arguments', array(
                'url'       => 'director/command/arguments',
                'urlParams' => array('name' => $this->object->object_name),
                'label'     => 'Arguments'
            ));
        }
    }

    public function argumentsAction()
    {
        $this->gracefullyActivateTab('arguments');
        $this->view->title = sprintf(
            $this->translate('Command arguments: %s'),
            $this->object->object_name
        );

        $this->view->table = $this
            ->loadTable('icingaCommandArgument')
            ->setCommandObject($this->object)
            ->setFilter(Filter::where('command', $this->params->get('name')));

        $form = $this->view->form = $this
            ->loadForm('icingaCommandArgument')
            ->setCommandObject($this->object);

        if ($id = $this->params->shift('argument_id')) {
            $form->loadObject($id);
        }

        $form->handleRequest();
    }
}
