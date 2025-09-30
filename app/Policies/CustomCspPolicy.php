<?php

namespace App\Policies;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Basic;

class CustomCspPolicy extends Basic
{
    public function configure()
    {
        parent::configure();

        // Allow connections to these sources
        $this
            ->addDirective(Directive::BASE, '*')
            ->addDirective(Directive::CONNECT, [
                '*',                           // Allow all connections (temporary)
                'ws:',                        // WebSocket
                'wss:',                       // Secure WebSocket
            ])
            ->addDirective(Directive::DEFAULT, '*')
            ->addDirective(Directive::FORM_ACTION, '*')
            ->addDirective(Directive::IMG, '*')
            ->addDirective(Directive::MEDIA, '*')
            ->addDirective(Directive::OBJECT, '*')
            ->addDirective(Directive::SCRIPT, '*')
            ->addDirective(Directive::STYLE, '*')
            ->addDirective(Directive::FONT, '*');
    }
}
