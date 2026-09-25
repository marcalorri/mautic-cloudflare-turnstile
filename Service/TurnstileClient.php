<?php

/*
 * @copyright   2024 Adapted for Cloudflare Turnstile. Original: 2018 Konstantin Scheumann.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticTurnstileBundle\Service;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Mautic\FormBundle\Entity\Field;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticTurnstileBundle\Integration\TurnstileIntegration;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Psr\Log\LoggerInterface;

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

    private $logger;

    private $client;

    /**
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper, LoggerInterface $logger, ClientInterface $client = null)
    {
        $this->logger = $logger;
        $this->client = $client ?: new GuzzleClient(['timeout' => 10]);
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
        try {
            $result = $this->client->request('POST', self::VERIFY_URL, [
                'http_errors' => false,
                'form_params' => [
                    'secret'   => $this->secretKey,
                    'response' => $response,
                ],
            ]);
        } catch (GuzzleException $exception) {
            $this->logger->warning('Turnstile verification request failed');

            return false;
        }

        $status = $result->getStatusCode();
        $result = json_decode($result->getBody(), true);
        if (!is_array($result)) {
            $this->logger->warning('Turnstile verification returned an invalid response');

            return false;
        }
        if ($status >= 200 && $status < 300 && isset($result['success']) && $result['success'] === true) {
            return true;
        }

        $allowedCodes = [
            'missing-input-secret', 'invalid-input-secret', 'missing-input-response',
            'invalid-input-response', 'bad-request', 'timeout-or-duplicate', 'internal-error',
        ];
        $codes = isset($result['error-codes']) && is_array($result['error-codes'])
            ? array_values(array_filter($result['error-codes'], function ($code) use ($allowedCodes) {
                return is_string($code) && in_array($code, $allowedCodes, true);
            }))
            : [];
        $this->logger->warning('Turnstile verification rejected', ['error_codes' => $codes]);

        return false;
    }
}
