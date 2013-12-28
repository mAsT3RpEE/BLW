<?php
/**
 * Bootsrap script that configures BLW Library.
 *
 * <h3>Introduction</h3>
 *
 * <p><code>BLW_PLUGIN_DIR</code> needs to be defined first or it
 * will trigger an error.</p>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 * @author Walter Otsyula <wotsyula@mast3rpee.tk>
 * @copyright 2013-2018 mAsT3RpEE's Zone
 * @license MIT
 */


/* BACKWARD COMPATABILITY */

;;;;


/* LIBRARIES */

require_once  dirname(__DIR__) . '/vendor/autoload.php';
require_once  __DIR__ . '/BLW/BLW.php';


/* INITIALIZATION */


BLW::Initialize();


/*-------------------------------------------------------------------- * /


return BLW::O('Form', true)
->action('#')
->method(\BLW\Model\Form::POST)
->push(BLW::O('Form\\Page', true)
    ->title('Step 1')
    ->push(BLW::O('Model\\Form\\Group', true)
        ->title('Login Form')
        ->push(BLW::O('Model\\Form\\Field\\Name')
            ->autocomplete(false)
            ->required(true)
            ->data('source', 'GoogleSearch')
        )
        ->push(BLW::O('Model\\Form\\Field\\Password')
            ->required(true)
            ->label('Password')
            ->min(4)
            ->max(38)
        )
        ->push(BLW::O('Model\\Form\\Button\\Submit', true)
            ->label('Login')
        )
        ->push(BLW::O('Model\\Form\\Button\\Button', true)
            ->label('Cancel')
            ->onClick('CancelForm(this);')
        )
    )
)
->walk(function($Event) {
    $Event->GetSubject()->Load()
})
->PrintHTML();

/*---------------------------------------------------------------------------------------------*/

return true;