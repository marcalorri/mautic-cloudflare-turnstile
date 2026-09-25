<?php

/*
 * @copyright   2024 Adapted for Cloudflare Turnstile. Original: 2018 Konstantin Scheumann.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Cloudflare Turnstile',
    'description' => 'Enables Cloudflare Turnstile integration.',
    'version'     => '1.1.0',
    'author'      => 'Konstantin Scheumann',

    'routes' => [

    ],

    'services' => [
        'events' => [
            'mautic.turnstile.event_listener.form_subscriber' => [
                'class'     => \MauticPlugin\MauticTurnstileBundle\EventListener\FormSubscriber::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.integration',
                    'mautic.turnstile.service.turnstile_client',
                    'mautic.lead.model.lead',
                    'translator'
                ],
            ],
        ],
        'models' => [

        ],
        'others'=>[
            'mautic.turnstile.service.turnstile_client' => [
                'class'     => \MauticPlugin\MauticTurnstileBundle\Service\TurnstileClient::class,
                'arguments' => [
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.turnstile' => [
                'class'     => \MauticPlugin\MauticTurnstileBundle\Integration\TurnstileIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ],
    'parameters' => [

    ],
];
