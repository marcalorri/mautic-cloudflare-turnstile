<?php

/*
 * @copyright   2024 Adapted for Cloudflare Turnstile. Original: 2018 Konstantin Scheumann.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticTurnstileBundle\Tests;

use Mautic\CoreBundle\Factory\ModelFactory;
use Mautic\FormBundle\Entity\Field;
use Mautic\FormBundle\Event\ValidationEvent;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticTurnstileBundle\EventListener\FormSubscriber;
use MauticPlugin\MauticTurnstileBundle\Integration\TurnstileIntegration;
use MauticPlugin\MauticTurnstileBundle\Service\TurnstileClient;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mautic\FormBundle\Event\FormBuilderEvent;
use Symfony\Component\Translation\TranslatorInterface;

class FormSubscriberTest extends TestCase
{
    /**
     * @var MockObject|TurnstileIntegration
     */
    private $integration;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MockObject|IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var MockObject|LeadModel
     */
    private $leadModel;

    /**
     * @var MockObject|TurnstileClient
     */
    private $turnstileClient;

    /**
     * @var MockObject|TranslatorInterface
     */
    private $translator;

    /**
     * @var MockBuilder|ValidationEvent
     */
    private $validationEvent;

    /**
     * @var MockBuilder|FormBuilderEvent
     */
    private $formBuildEvent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration       = $this->createMock(TurnstileIntegration::class);
        $this->eventDispatcher   = $this->createMock(EventDispatcherInterface::class);
        $this->integrationHelper = $this->createMock(IntegrationHelper::class);
        $this->leadModel         = $this->createMock(LeadModel::class);
        $this->turnstileClient   = $this->createMock(TurnstileClient::class);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->validationEvent   = $this->createMock(ValidationEvent::class);
        $this->formBuildEvent    = $this->createMock(FormBuilderEvent::class);

        $this->eventDispatcher
            ->method('addListener')
            ->willReturn(true);

        $this->integration
            ->method('getKeys')
            ->willReturn(['site_key' => 'test', 'secret_key' => 'test']);

        $this->validationEvent
            ->method('getValue')
            ->willReturn('test');

        $this->validationEvent
            ->method('getField')
            ->willReturn(new Field());
    }

    public function testOnFormValidateSuccessful()
    {
        $this->turnstileClient->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn($this->integration);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormValidateFailure()
    {
        $this->turnstileClient->expects($this->once())
            ->method('verify')
            ->willReturn(false);

        $this->validationEvent->expects($this->once())
            ->method('getValue')
            ->willReturn('any-value-should-work');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn($this->integration);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormValidateWhenPluginIsNotInstalled()
    {
        $this->turnstileClient->expects($this->never())
            ->method('verify');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(null);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormValidateWhenPluginIsNotConfigured()
    {
        $this->turnstileClient->expects($this->never())
            ->method('verify');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(['site_key' => '']);

        $this->createFormSubscriber()->onFormValidate($this->validationEvent);
    }

    public function testOnFormBuildWhenPluginIsInstalledAndConfigured()
    {
        $this->formBuildEvent->expects($this->once())
            ->method('addFormField')
            ->with('plugin.turnstile');

        $this->formBuildEvent->expects($this->once())
            ->method('addValidator')
            ->with('plugin.turnstile.validator');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn($this->integration);

        $this->createFormSubscriber()->onFormBuild($this->formBuildEvent);
    }

    public function testOnFormBuildWhenPluginIsNotInstalled()
    {
        $this->formBuildEvent->expects($this->never())
            ->method('addFormField');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(null);

        $this->createFormSubscriber()->onFormBuild($this->formBuildEvent);
    }

    public function testOnFormBuildWhenPluginIsNotConfigured()
    {
        $this->formBuildEvent->expects($this->never())
            ->method('addFormField');

        $this->integrationHelper->expects($this->once())
            ->method('getIntegrationObject')
            ->willReturn(['site_key' => '']);

        $this->createFormSubscriber()->onFormBuild($this->formBuildEvent);
    }

    /**
     * @return FormSubscriber
     */
    private function createFormSubscriber()
    {
        return new FormSubscriber(
            $this->eventDispatcher,
            $this->integrationHelper,
            $this->turnstileClient,
            $this->leadModel,
            $this->translator
        );
    }
}
