<?php
/*
 * This file is part of the prooph processing framework.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 10.01.15 - 18:16
 */

namespace Prooph\ProcessingTest\Processor;

use Prooph\Processing\Message\ProcessingMessage;
use Prooph\Processing\Message\LogMessage;
use Prooph\Processing\Message\ProophPlugin\ToProcessingMessageTranslator;
use Prooph\Processing\Message\WorkflowMessage;
use Prooph\Processing\Processor\AbstractWorkflowEngine;
use Prooph\Processing\Processor\Command\StartSubProcess;
use Prooph\Processing\Processor\Event\SubProcessFinished;
use Prooph\Processing\Processor\NodeName;
use Prooph\Processing\Processor\ProcessId;
use Prooph\Processing\Processor\RegistryWorkflowEngine;
use Prooph\Processing\Processor\Task\TaskListId;
use Prooph\Processing\Processor\Task\TaskListPosition;
use Prooph\ProcessingTest\Mock\UserDictionary;
use Prooph\ProcessingTest\TestCase;
use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\InvokeStrategy\CallbackStrategy;
use Prooph\ServiceBus\Router\CommandRouter;
use Prooph\ServiceBus\Router\EventRouter;

/**
 * Class AbstractWorkflowEngineTest
 *
 * @package Prooph\ProcessingTest\Processor
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class AbstractWorkflowEngineTest extends TestCase
{
    /**
     * @var AbstractWorkflowEngine
     */
    protected $workflowEngine;

    /**
     * @var ProcessingMessage
     */
    private $receivedMessage;

    protected function setUp()
    {
        parent::setUp();

        $eventBus = new EventBus();

        $commandBus = new CommandBus();

        $commandRouter = new CommandRouter();

        $commandRouter->route(StartSubProcess::MSG_NAME)->to(function (StartSubProcess $command) {
            $this->receivedMessage = $command;
        });

        $commandRouter->route('processing-message-proophprocessingtestmockuserdictionary-collect-data')
            ->to(function (WorkflowMessage $message) {
                $this->receivedMessage = $message;
            });

        $commandBus->utilize($commandRouter);

        $commandBus->utilize(new ToProcessingMessageTranslator());

        $commandBus->utilize(new CallbackStrategy());

        $eventRouter = new EventRouter();

        $eventRouter->route('processing-message-proophprocessingtestmockuserdictionary-data-collected')
            ->to(function (WorkflowMessage $workflowMessage) {
                $this->receivedMessage = $workflowMessage;
            });

        $eventRouter->route('processing-log-message')
            ->to(function (LogMessage $logMessage) {
                $this->receivedMessage = $logMessage;
            });

        $eventRouter->route(SubProcessFinished::MSG_NAME)->to(function (SubProcessFinished $event) {
            $this->receivedMessage = $event;
        });

        $eventBus->utilize($eventRouter);

        $eventBus->utilize(new ToProcessingMessageTranslator());

        $eventBus->utilize(new CallbackStrategy());

        $this->workflowEngine = new RegistryWorkflowEngine();

        $this->workflowEngine->registerCommandBus($commandBus, [NodeName::defaultName()->toString(), 'test-target', 'sub-processor']);
        $this->workflowEngine->registerEventBus($eventBus, [NodeName::defaultName()->toString()]);
    }

    protected function tearDown()
    {
        $this->receivedMessage = null;
    }

    /**
     * @test
     */
    public function it_dispatches_a_workflow_message_command()
    {
        $wfMessage = WorkflowMessage::collectDataOf(UserDictionary::prototype(),'test-case', 'test-target', []);

        $this->workflowEngine->dispatch($wfMessage);

        $this->assertSame($wfMessage, $this->receivedMessage);
    }

    /**
     * @test
     */
    public function it_dispatches_a_workflow_message_event()
    {
        $userData = array(
            'id' => 1,
            'name' => 'Alex',
            'address' => array(
                'street' => 'Main Street',
                'streetNumber' => 10,
                'zip' => '12345',
                'city' => 'Test City'
            )
        );

        $user = UserDictionary::fromNativeValue($userData);

        $wfMessage = WorkflowMessage::newDataCollected($user, 'test-case', NodeName::defaultName(), []);

        $this->workflowEngine->dispatch($wfMessage);

        $this->assertSame($wfMessage, $this->receivedMessage);
    }

    /**
     * @test
     */
    public function it_dispatches_a_log_message()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $wfMessage = $this->getUserDataCollectedTestMessage();

        $wfMessage->connectToProcessTask($taskListPosition);

        $logMessage = LogMessage::logWarningMsg("Just a fake warning", $wfMessage);

        $this->workflowEngine->dispatch($logMessage);

        $this->assertSame($logMessage, $this->receivedMessage);
    }

    /**
     * @test
     */
    public function it_dispatches_a_start_sub_process_command()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $startSupProcess = StartSubProcess::at($taskListPosition, ['process_type' => 'faked'], true, 'sub-processor');

        $this->workflowEngine->dispatch($startSupProcess);

        $this->assertSame($startSupProcess, $this->receivedMessage);
    }

    /**
     * @test
     */
    public function it_dispatches_a_sub_process_finished_event()
    {
        $taskListPosition = TaskListPosition::at(TaskListId::linkWith(NodeName::defaultName(), ProcessId::generate()), 1);

        $wfMessage = $this->getUserDataCollectedTestMessage();

        $wfMessage->connectToProcessTask($taskListPosition);

        $logMessage = LogMessage::logDebugMsg("Just a fake event", $wfMessage);

        $subProcessFinished = SubProcessFinished::record(
            NodeName::defaultName(),
            $taskListPosition->taskListId()->processId(),
            true,
            $logMessage,
            $taskListPosition
        );

        $this->workflowEngine->dispatch($subProcessFinished);

        $this->assertSame($subProcessFinished, $this->receivedMessage);
    }

    /**
     * @test
     */
    public function it_dispatches_a_service_bus_message()
    {
        $wfMessage = WorkflowMessage::collectDataOf(UserDictionary::prototype(), 'test-case', 'test-target', []);

        $sbMessage = $wfMessage->toServiceBusMessage();

        $this->workflowEngine->dispatch($sbMessage);

        $this->assertInstanceOf(get_class($wfMessage), $this->receivedMessage);
    }
}
 