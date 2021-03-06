<?php
/*
 * This file is part of the prooph processing framework.
 * (c) prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 30.01.15 - 08:19
 */

namespace Prooph\Processing\Functional\Iterator;

/**
 * Class MapIterator
 *
 * @package Prooph\Processing\Functional\Iterator
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
final class MapIterator extends \IteratorIterator
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct(\Traversable $iterator, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Provided callback should be callable. Got " . gettype($callback));
        }
        parent::__construct($iterator);
        $this->callback = $callback;
    }

    public function current()
    {
        $iterator = $this->getInnerIterator();

        $callback = $this->callback;

        return $callback(parent::current(), parent::key(), $iterator);
    }
}
 