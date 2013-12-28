<?php
class NewArrayObject extends \BLW\Type\Adaptor
{
    /**
     * @var string TARGET_CLASS Used by GetInstance to generate instance of class
     */
    protected static $_Class = '\\ArrayIterator';
}