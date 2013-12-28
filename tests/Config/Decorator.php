<?php
class DecoratedObject extends \BLW\Type\Decorator
{
    public function DecorateDo($Name, \BLW\Interfaces\Event $Event, \BLW\Interfaces\Object $Subject)
    {
        $GLOBALS['DECORATE_TEST'] = sprintf('Doing decorator (%s): %s', $Name, preg_replace('/\s+/', ' ', print_r($Event, true)));
        return $this;
    }

    public function DecorateOn($Name, $Function, \BLW\Interfaces\Object $Subject)
    {
        $GLOBALS['DECORATE_TEST'] = sprintf('Registering decorator (%s): %s', $Name, preg_replace('/\s+/', ' ', print_r($Function, true)));
        return $this;
    }

    public function DecorateToString($String, \BLW\Interfaces\Object $Subject)
    {
        $GLOBALS['DECORATE_TEST'] = sprintf('strval decorator: %s', $String);
        return $String;
    }

}