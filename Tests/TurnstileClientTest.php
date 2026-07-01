<?php

/*
 * @copyright   2024 Adapted for Cloudflare Turnstile. Original: 2018 Konstantin Scheumann.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticTurnstileBundle\Tests;

use Mautic\FormBundle\Entity\Field;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockBuilder;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticTurnstileBundle\Integration\TurnstileIntegration;
use MauticPlugin\MauticTurnstileBundle\Service\TurnstileClient;

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

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationHelper = $this->createMock(IntegrationHelper::class);
        $this->integration       = $this->createMock(TurnstileIntegration::class);
        $this->field = new Field();
    }

    public function testVerifyWhenPluginIsNotInstalled()
    {
        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(null);

        $this->integration->expects($this->never())
            ->method('getKeys');

        $this->createTurnstileClient()->verify('', $this->field);
    }

    public function testVerifyWhenPluginIsNotConfigured()
    {
        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn($this->integration);

        $this->integration->expects($this->once())
            ->method('getKeys')
            ->willReturn(['site_key' => 'test', 'secret_key' => 'test']);

        $this->createTurnstileClient()->verify('', $this->field);
    }

    /**
     * @return TurnstileClient
     */
    private function createTurnstileClient()
    {
        return new TurnstileClient(
            $this->integrationHelper
        );
    }
}
