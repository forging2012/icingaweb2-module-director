<?php

namespace Icinga\Module\Director\Controllers;

use Exception;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Director\IcingaConfig\AgentWizard;
use Icinga\Module\Director\Objects\IcingaEndpoint;
use Icinga\Module\Director\Objects\IcingaZone;
use Icinga\Module\Director\Util;
use Icinga\Module\Director\Web\Controller\ObjectController;

class HostController extends ObjectController
{
    public function init()
    {
        parent::init();
        if ($this->object) {
            $tabs = $this->getTabs();
            $tabs->add('services', array(
                'url'       => 'director/host/services',
                'urlParams' => array('name' => $this->object->object_name),
                'label'     => 'Services'
            ));
            if ($this->object->object_type === 'object'
                && $this->object->getResolvedProperty('has_agent') === 'y'
            ) {
                $tabs->add('agent', array(
                    'url'       => 'director/host/agent',
                    'urlParams' => array('name' => $this->object->object_name),
                    'label'     => 'Agent'
                ));
            }
        }
    }

    public function editAction()
    {
        parent::editAction();
        $host = $this->object;
        $mon = $this->monitoring();
        if ($host->isObject() && $mon->isAvailable() && $mon->hasHost($host->object_name)) {
            $this->view->actionLinks .= ' ' . $this->view->qlink(
                $this->translate('Show'),
                'monitoring/host/show',
                array('host' => $host->object_name),
                array(
                    'class'            => 'icon-globe critical',
                    'data-base-target' => '_next'
                )
            );
        }
    }

    public function servicesAction()
    {
        $host = $this->object;

        $this->view->addLink = $this->view->qlink(
            $this->translate('Add service'),
            'director/service/add',
            array('host' => $host->object_name),
            array('class' => 'icon-plus')
        );

        $this->getTabs()->activate('services');
        $this->view->title = sprintf(
            $this->translate('Services: %s'),
            $host->object_name
        );
        $this->view->table = $this->loadTable('IcingaHostService')
            ->setHost($host)
            ->enforceFilter('host_id', $host->id)
            ->setConnection($this->db());
    }

    public function agentAction()
    {
        switch ($this->params->get('download')) {
            case 'windows-kickstart':
                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename=icinga2-agent-kickstart.ps1');

                $wizard = $this->view->wizard = new AgentWizard($this->object);
                $wizard->setTicketSalt($this->api()->getTicketSalt());
                echo preg_replace('/\n/', "\r\n", $wizard->renderWindowsInstaller());
                exit;
        }

        $this->gracefullyActivateTab('agent');
        $this->view->title = 'Agent deployment instructions';
        // TODO: Fail when no ticket
        $this->view->certname = $this->object->object_name;

        try {
            $this->view->ticket = Util::getIcingaTicket(
                $this->view->certname,
                $this->api()->getTicketSalt()
            );

            $wizard = $this->view->wizard = new AgentWizard($this->object);
            $wizard->setTicketSalt($this->api()->getTicketSalt());
            $this->view->windows = $wizard->renderWindowsInstaller();

        } catch (Exception $e) {
            $this->view->ticket = 'ERROR';
            $this->view->error = sprintf(
                $this->translate(
                    'A ticket for this agent could not have been requested from'
                    . ' your deployment endpoint: %s'
                ),
                $e->getMessage()
            );
        }

        $this->view->master = $this->db()->getDeploymentEndpointName();
        $this->view->masterzone = $this->db()->getMasterZoneName();
        $this->view->globalzone = $this->db()->getDefaultGlobalZoneName();
    }

    public function ticketAction()
    {
        if (! $this->getRequest()->isApiRequest() || ! $this->object) {
            throw new NotFoundError('Not found');
        }

        $host = $this->object;
        if ($host->getResolvedProperty('has_agent') !== 'y') {
            throw new NotFoundError('The host "%s" is not an agent', $host->object_name);
        }

        return $this->sendJson(
            Util::getIcingaTicket(
                $host->object_name,
                $this->api()->getTicketSalt()
            )
        );
    }
}
