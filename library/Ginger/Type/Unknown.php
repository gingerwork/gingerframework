<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 09.12.14 - 18:20
 */

namespace Ginger\Type;

use Ginger\Type\Description\Description;
use Ginger\Type\Description\NativeType;
use Ginger\Type\Exception\InvalidTypeException;

/**
 * Class Unknown
 *
 * An Unknown can have any scalar or array or a mix of both as value but no objects or resources
 *
 * @package Ginger\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class Unknown extends SingleValue
{
    /**
     * Performs assertions and sets the internal value property on success
     *
     * @param mixed $value
     * @return void
     */
    protected function setValue($value)
    {
        $this->assertIsScalarOrArray($value);

        $this->value = $value;
    }

    /**
     * @return Description
     */
    public static function buildDescription()
    {
        return new Description('Unknown', NativeType::UNKNOWN, false);
    }

    /**
     * @param string $valueString
     * @return Type
     */
    public static function fromString($valueString)
    {
        return Unknown::fromNativeValue(json_decode($valueString, true));
    }

    /**
     * @return string representation of the value
     */
    public function toString()
    {
        return json_encode($this->value);
    }

    /**
     * This method checks recursively if the value only consists of arrays and scalar types
     *
     * @param $value
     * @throws Exception\InvalidTypeException
     */
    private function assertIsScalarOrArray($value)
    {
        if (! is_scalar($value) && !is_array($value)) {
            throw InvalidTypeException::fromMessageAndPrototype("An unknown type must at least be a scalar or array value", static::prototype());
        }

        if (is_array($value)) {
            foreach ($value as $item) $this->assertIsScalarOrArray($item);
        }
    }
}
 