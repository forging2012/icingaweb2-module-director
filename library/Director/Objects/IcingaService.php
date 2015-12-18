<?php

namespace Icinga\Module\Director\Objects;

class IcingaService extends IcingaObject
{
    protected $table = 'icinga_service';

    protected $defaultProperties = array(
        'id'                    => null,
        'object_name'           => null,
        'display_name'          => null,
        'host_id'               => null,
        'check_command_id'      => null,
        'max_check_attempts'    => null,
        'check_period_id'       => null,
        'check_interval'        => null,
        'retry_interval'        => null,
        'enable_notifications'  => null,
        'enable_active_checks'  => null,
        'enable_passive_checks' => null,
        'enable_event_handler'  => null,
        'enable_flapping'       => null,
        'enable_perfdata'       => null,
        'event_command_id'      => null,
        'flapping_threshold'    => null,
        'volatile'              => null,
        'zone_id'               => null,
        'command_endpoint_id'   => null,
        'notes'                 => null,
        'notes_url'             => null,
        'action_url'            => null,
        'icon_image'            => null,
        'icon_image_alt'        => null,
        'object_type'           => null,
        'use_agent'             => null,
    );

    protected $relations = array(
        'host'             => 'IcingaHost',
        'check_command'    => 'IcingaCommand',
        'event_command'    => 'IcingaCommand',
        'check_period'     => 'IcingaTimePeriod',
        'command_endpoint' => 'IcingaEndpoint',
        'zone'             => 'IcingaZone',
    );

    protected $supportsGroups = true;

    protected $supportsCustomVars = true;

    protected $supportsFields = true;

    protected $supportsImports = true;

    protected $keyName = array('host_id', 'object_name');

    public function getCheckCommand()
    {
        $id = $this->getResolvedProperty('check_command_id');
        return IcingaCommand::loadWithAutoIncId(
            $id,
            $this->getConnection()
        );
    }

    public function renderHost_id()
    {
        return $this->renderRelationProperty('host', $this->host_id, 'host_name');
    }

    public function renderUse_agent()
    {
        return '';
    }

    protected function renderCheck_Interval()
    {
        return $this->renderPropertyAsSeconds('check_interval');
    }

    protected function renderRetry_Interval()
    {
        return $this->renderPropertyAsSeconds('retry_interval');
    }

    public function hasCheckCommand()
    {
        return $this->getResolvedProperty('check_command_id') !== null;
    }

    protected function renderCheck_command_id()
    {
        return $this->renderCommandProperty($this->check_command_id);
    }

    protected function renderEnable_notifications()
    {
        return $this->renderBooleanProperty('enable_notifications');
    }

    protected function renderEnable_active_checks()
    {
        return $this->renderBooleanProperty('enable_active_checks');
    }

    protected function renderEnable_passive_checks()
    {
        return $this->renderBooleanProperty('enable_passive_checks');
    }

    protected function renderEnable_event_handler()
    {
        return $this->renderBooleanProperty('enable_event_handler');
    }

    protected function renderEnable_flapping()
    {
        return $this->renderBooleanProperty('enable_flapping');
    }

    protected function renderEnable_perfdata()
    {
        return $this->renderBooleanProperty('enable_perfdata');
    }

    protected function renderVolatile()
    {
        return $this->renderBooleanProperty('volatile');
    }
}
