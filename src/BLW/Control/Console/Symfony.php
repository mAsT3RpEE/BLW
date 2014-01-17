<?php
/**
 * APP.run.php | Dec 16, 2013
 *
 * Copyright (c) mAsT3RpEE's Zone
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @filesource
 * @copyright mAsT3RpEE's Zone
 * @license MIT
 */

/**
 * @package BLW\Core
 * @version 1.0.0
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 */

namespace BLW\Control\Console;

use BLW;

/**
 * Console application that builds PHAR files and optimizez images / scripts / etc.
 * @package BLW\Core
 * @author mAsT3RpEE <wotsyula@mast3rpee.tk>
 */
class Symfony extends \BLW\Type\Singleton implements \BLW\Interfaces\Control
{
    const DEFAULT_ACTION = 'run';

    /**
     * Hook that is called when a new instance is created.
     * @return \BLW\Interfaces\Object $this
     */
    public static function doCreate()
    {
        $self   = parent::doCreate();
        $Logger = BLW::Logger('application');

        // Commands
        $self[] = \BLW\Model\ApplicationCommand\Symfony\Download::GetInstance(array('Logger' => $Logger));
        $self[] = \BLW\Model\ApplicationCommand\Symfony\Update::GetInstance(array('Logger' => $Logger));
        $self[] = \BLW\Model\ApplicationCommand\Symfony\Compile::GetInstance(array('Logger' => $Logger));
        $self[] = \BLW\Model\ApplicationCommand\Symfony\Test::GetInstance(array('Logger' => $Logger));
        $self[] = \BLW\Model\ApplicationCommand\Symfony\Build::GetInstance(array('Logger' => $Logger));

        return BLW::$Self = $self;
    }

    /**
     * Performs a control action.
     * @param string $Action action to perform.
     * @return \BLW\Interfaces\Singleton $this
     */
    public function doAction($Action)
    {
        $StringAction = @strtolower($Action);

        if (is_callable(array($this, $StringAction))) {
            BLW::Logger('application')->debug(sprintf('Performing action: %s::%s', get_class($this), $StringAction));
            call_user_func(array($this, $StringAction));
        }

        else {
            BLW::Logger('application')->warning(sprintf('Unknown action %s::%s', get_class($this), $StringAction));
            call_user_func(array($this, self::DEFAULT_ACTION));
        }

        return $this;
    }

    /**
     * Runs the console application.
     * @return void
     */
    public function run()
    {
        if (($Application = BLW::GetView()) instanceof \BLW\Interfaces\Application) {

            $Logger = BLW::Logger('application');

            // Set console timeout
            if(is_callable('set_time_limit')) {
                $Logger->debug('Disabling php time limit.');

                set_time_limit(0);
            }

            foreach ($this as $Command) {
                $Application->push($Command);
            }
        }

        else {
            trigger_error(sprintf('%s:run(): current view is not an application.', get_class($this)), E_USER_WARNING);
            return 0;
        }

        return $this;
    }
}