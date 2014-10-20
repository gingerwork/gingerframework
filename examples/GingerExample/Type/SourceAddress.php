<?php
/*
 * This file is part of the Ginger Workflow Framework.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 19.10.14 - 22:37
 */

namespace GingerExample\Type;

use Ginger\Type\AbstractDictionary;
use Ginger\Type\Description\Description;
use Ginger\Type\Description\NativeType;
use Ginger\Type\Integer;
use Ginger\Type\String;

/**
 * Class SourceAddress
 *
 * @package GingerExample\Type
 * @author Alexander Miertsch <kontakt@codeliner.ws>
 */
class SourceAddress extends AbstractDictionary
{
    /**
     * @return array[propertyName => Prototype]
     */
    public static function getPropertyPrototypes()
    {
        return array(
            'street' => String::prototype(),
            'streetNumber' => Integer::prototype(),
            'zip' => String::prototype(),
            'city' => String::prototype()
        );
    }

    /**
     * @return Description
     */
    public static function buildDescription()
    {
        return new Description("Address", NativeType::DICTIONARY, false);
    }
}
 