<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.10.14 - 23:34
 */

namespace GingerTest\Processor;

use Ginger\Processor\RegistryWorkflowEngine;
use GingerTest\TestCase;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;

/**
 * Class RegistryWorkflowEngineTest
 *
 * @package GingerTest\Processor
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class RegistryWorkflowEngineTest extends TestCase
{
    /**
     * @test
     */
    public function it_provides_command_bus_for_target()
    {
        $commandBus = $this->getTestWorkflowEngine()->getCommandBusFor('crm');

        $this->assertInstanceOf('Prooph\ServiceBus\CommandBus', $commandBus);
    }

    /**
     * @test
     */
    public function it_provides_event_bus_for_target()
    {
        $eventBus = $this->getTestWorkflowEngine()->getEventBusFor('wawi');

        $this->assertInstanceOf('Prooph\ServiceBus\EventBus', $eventBus);
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_register_second_command_bus_for_same_target()
    {
        $this->setExpectedException('\RuntimeException');

        $this->getTestWorkflowEngine()->registerCommandBus(new CommandBus(), ['crm']);
    }

    /**
     * @test
     */
    public function it_does_not_allow_to_register_second_event_bus_for_same_target()
    {
        $this->setExpectedException('\RuntimeException');

        $this->getTestWorkflowEngine()->registerEventBus(new EventBus(), ['crm']);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_no_command_bus_is_registered_for_target()
    {
        $this->setExpectedException('\RuntimeException');

        $this->getTestWorkflowEngine()->getCommandBusFor('unknown');
    }

    /**
     * @test
     */
    public function it_throws_exception_if_no_event_bus_is_registered_for_target()
    {
        $this->setExpectedException('\RuntimeException');

        $this->getTestWorkflowEngine()->getEventBusFor('unknown');
    }

    /**
     * @return RegistryWorkflowEngine
     */
    protected function getTestWorkflowEngine()
    {
        $workflowEngine = new RegistryWorkflowEngine();

        $commandBus = new CommandBus();

        $workflowEngine->registerCommandBus($commandBus, ['crm', 'online-shop']);

        $eventBus = new EventBus();

        $workflowEngine->registerEventBus($eventBus, ['crm', 'wawi']);

        return $workflowEngine;
    }
}
 