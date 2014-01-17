<?php
class NewArrayObject extends \BLW\Type\Adaptor
{
    /**
     * @var string TARGET_CLASS Used by GetInstance to generate instance of class
     */
    protected static $_Class = '\\ArrayIterator';

    /**
     * Hook that is called just before an object is serialized.
     * @return \BLW\Interfaces\Adaptor $this
     */
    public function doSerialize() {return $this;}

    /**
     * Hook that is called just after an object is unserialized.
     * @return \BLW\Interfaces\Adaptor $this
     */
    public function doUnSerialize() {return $this;}
}