<?php

class mySoapServer
{
    public function ping()
    {
        return 'PONG';
    }
}

if (!class_exists('SOAPServer')) {
    die('SOAPServer not supported.');
}

try {

    $server = new SOAPServer(
        null,
        array(
            'uri' => sprintf('http://%s%s', $_SERVER['SERVER_NAME'], $_SERVER['SCRIPT_NAME'])
        )
    );

    $server->setClass('mySoapServer');
    $server->handle();

} catch (SOAPFault $f) {

    echo $f->faultstring;
}
