<?php

/*
 * @copyright   2024 Adapted for Cloudflare Turnstile. Original: 2018 Konstantin Scheumann.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticTurnstileBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use Mautic\FormBundle\Entity\Field;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticTurnstileBundle\Integration\TurnstileIntegration;
use Mautic\PluginBundle\Integration\AbstractIntegration;

class TurnstileClient
{
    const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * @var string
     */
    protected $siteKey;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $integrationObject = $integrationHelper->getIntegrationObject(TurnstileIntegration::INTEGRATION_NAME);

        if ($integrationObject instanceof AbstractIntegration) {
            $keys            = $integrationObject->getKeys();
            $this->siteKey   = isset($keys['site_key']) ? $keys['site_key'] : null;
            $this->secretKey = isset($keys['secret_key']) ? $keys['secret_key'] : null;
        }
    }

    /**
     * @param string $response
     * @param Field  $field
     *
     * @return bool
     */
    public function verify($response, Field $field)
    {
        $client   = new GuzzleClient(['timeout' => 10]);
        $response = $client->post(
            self::VERIFY_URL,
            [
                'form_params' => [
                    'secret'   => $this->secretKey,
                    'response' => $response,
                ],
            ]
        );

        $response = json_decode($response->getBody(), true);

        return array_key_exists('success', $response) && $response['success'] === true;
    }
}
