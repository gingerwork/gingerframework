<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 03.10.14 - 23:52
 */

namespace Ginger\Processor\Event;

use Ginger\Processor\ProcessId;
use Ginger\Processor\Task\TaskList;
use Ginger\Processor\Task\TaskListPosition;
use Prooph\EventSourcing\AggregateChanged;

/**
 * Class ProcessSetUp Event
 *
 * @package Ginger\Processor\Event
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class ProcessSetUp extends AggregateChanged
{
    /**
     * @var ProcessId
     */
    private $processId;

    /**
     * @var TaskListPosition
     */
    private $parentTaskListPosition;

    /**
     * @param ProcessId $processId
     * @param TaskList $taskList
     * @param array $config
     * @return ProcessSetUp
     */
    public static function with(ProcessId $processId, TaskList $taskList, array $config)
    {
        $instance = self::occur(
            $processId->toString(),
            [
                'config' => $config,
                'parent_task_list_Position' => null,
                'task_list' => $taskList->getArrayCopy()
            ]
        );

        $instance->processId = $processId;

        return $instance;
    }

    /**
     * @param ProcessId $processId
     * @param TaskListPosition $parentTaskListPosition
     * @param TaskList $taskList
     * @param array $config
     * @return static
     */
    public static function asSubProcess(ProcessId $processId, TaskListPosition $parentTaskListPosition, TaskList $taskList, array $config)
    {
        $instance = self::occur(
            $processId->toString(),
            [
                'config' => $config,
                'parent_task_list_Position' => $parentTaskListPosition->toString(),
                'task_list' => $taskList->getArrayCopy()
            ]
        );

        $instance->processId = $processId;

        $instance->parentTaskListPosition = $parentTaskListPosition;

        return $instance;
    }

    /**
     * @return ProcessId
     */
    public function processId()
    {
        if (is_null($this->processId)) {
            $this->processId = ProcessId::fromString($this->aggregateId);
        }

        return $this->processId;
    }

    /**
     * @return TaskListPosition|null
     */
    public function parentTaskListPosition()
    {
        if (is_null($this->parentTaskListPosition) && ! is_null($this->payload['parent_task_list_Position'])) {
            $this->parentTaskListPosition = TaskListPosition::fromString($this->payload['parent_task_list_Position']);
        }

        return $this->parentTaskListPosition;
    }

    /**
     * @return array
     */
    public function config()
    {
        return $this->payload['config'];
    }

    /**
     * @return array[taskListId => string, entries => entryArr[]]
     */
    public function taskList()
    {
        return $this->payload['task_list'];
    }
}
 