<?php

/*
 * @copyright   2024 Adapted for Cloudflare Turnstile. Original: 2018 Konstantin Scheumann.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticTurnstileBundle\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mautic\FormBundle\Entity\Field;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticTurnstileBundle\Integration\TurnstileIntegration;
use MauticPlugin\MauticTurnstileBundle\Service\TurnstileClient;
use Psr\Log\LoggerInterface;

class TurnstileClientTest extends TestCase
{
    /**
     * @var MockObject|IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var MockObject|TurnstileIntegration
     */
    private $integration;

    /**
     * @var Field
     */
    private $field;

    private $client;

    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationHelper = $this->createMock(IntegrationHelper::class);
        $this->integration       = $this->createMock(TurnstileIntegration::class);
        $this->client            = $this->createMock(ClientInterface::class);
        $this->logger            = $this->createMock(LoggerInterface::class);
        $this->field             = new Field();

        $this->integrationHelper->method('getIntegrationObject')->willReturn($this->integration);
        $this->integration->method('getKeys')->willReturn(['site_key' => 'site-value', 'secret_key' => 'secret-value']);
    }

    public function testVerifySuccess()
    {
        $this->client->expects($this->once())->method('request')
            ->with('POST', TurnstileClient::VERIFY_URL, [
                'http_errors' => false,
                'form_params' => ['secret' => 'secret-value', 'response' => 'token-value'],
            ])
            ->willReturn(new Response(200, [], '{"success":true}'));
        $this->logger->expects($this->never())->method('warning');

        $this->assertTrue($this->createTurnstileClient()->verify('token-value', $this->field));
    }

    public function testVerifyFailureLogsOnlyKnownCodes()
    {
        $this->client->method('request')->willReturn(new Response(200, [], json_encode([
            'success'     => false,
            'error-codes' => ['timeout-or-duplicate', 'token-value', 'secret-value'],
        ])));
        $this->logger->expects($this->once())->method('warning')
            ->with('Turnstile verification rejected', ['error_codes' => ['timeout-or-duplicate']]);

        $this->assertFalse($this->createTurnstileClient()->verify('token-value', $this->field));
    }

    public function testVerifyHttpErrorWithCodes()
    {
        $this->client->method('request')->willReturn(new Response(400, [], '{"success":false,"error-codes":["bad-request"]}'));
        $this->logger->expects($this->once())->method('warning')
            ->with('Turnstile verification rejected', ['error_codes' => ['bad-request']]);

        $this->assertFalse($this->createTurnstileClient()->verify('token-value', $this->field));
    }

    public function testVerifyHttpErrorNeverSucceeds()
    {
        $this->client->method('request')->willReturn(new Response(500, [], '{"success":true}'));
        $this->logger->expects($this->once())->method('warning')
            ->with('Turnstile verification rejected', ['error_codes' => []]);

        $this->assertFalse($this->createTurnstileClient()->verify('token-value', $this->field));
    }

    public function testVerifyInvalidResponseDoesNotLogBody()
    {
        $this->client->method('request')->willReturn(new Response(200, [], 'secret-value'));
        $this->logger->expects($this->once())->method('warning')
            ->with('Turnstile verification returned an invalid response');

        $this->assertFalse($this->createTurnstileClient()->verify('token-value', $this->field));
    }

    public function testVerifyRequestFailureDoesNotLogException()
    {
        $this->client->method('request')->willThrowException(
            new ConnectException('secret-value', new Request('POST', TurnstileClient::VERIFY_URL))
        );
        $this->logger->expects($this->once())->method('warning')
            ->with('Turnstile verification request failed');

        $this->assertFalse($this->createTurnstileClient()->verify('token-value', $this->field));
    }

    /**
     * @return TurnstileClient
     */
    private function createTurnstileClient()
    {
        return new TurnstileClient($this->integrationHelper, $this->logger, $this->client);
    }
}
