<?php
// last modified 1 April 2001 by John Doe

$CFG1 = array(
	'owner' => array(
	   'name'          => 'John Doe',
        'organization' => 'Acme Widgets Inc.'
    )
);

// use IP address in case network name resolution is not working
$CFG2 = array(
	'database' => array(
	   'server'        => '192.0.2.62',
	    'port'         => 143,
	    'file'         => 'payroll.dat'
    )
);
?>