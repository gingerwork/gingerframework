<?php
/*
 * This file is part of the prooph processing framework.
 * (c) 2014-2015 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 08.07.14 - 21:26
 */

namespace Prooph\Processing\Type;

use Prooph\Processing\Type\Description\Description;
use Prooph\Processing\Type\Description\DescriptionRegistry;
use Prooph\Processing\Type\Description\NativeType;
use Prooph\Processing\Type\Exception\InvalidTypeException;

/**
 * Class Integer
 *
 * SingleValue type integer
 *
 * @package Prooph\Processing\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Integer extends SingleValue
{
    /**
     * The description is cached in the internal description property
     *
     * @return Description
     */
    public static function buildDescription()
    {
        if (DescriptionRegistry::hasDescription(__CLASS__)) return DescriptionRegistry::getDescription(__CLASS__);

        $desc = new Description('Integer', NativeType::INTEGER, false);

        DescriptionRegistry::registerDescriptionFor(__CLASS__, $desc);

        return $desc;
    }

    /**
     * @param string $valueString
     * @return Type
     */
    public static function fromString($valueString)
    {
        return new static(intval($valueString));
    }

    /**
     * Performs assertions and sets the internal value property on success
     *
     * @param mixed $value
     * @return void
     */
    protected function setValue($value)
    {
        if (!is_int($value)) {
            throw InvalidTypeException::fromMessageAndPrototype("Value must be an integer", static::prototype());
        }
        $this->value = $value;
    }
}
 