<?php

/*
 * @copyright   2024 Adapted for Cloudflare Turnstile. Original: 2018 Konstantin Scheumann.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticTurnstileBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

/**
 * Class TurnstileIntegration.
 */
class TurnstileIntegration extends AbstractIntegration
{
    const INTEGRATION_NAME = 'Turnstile';

    public function getName()
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName()
    {
        return 'Cloudflare Turnstile';
    }

    public function getAuthenticationType()
    {
        return 'none';
    }

    public function getRequiredKeyFields()
    {
        return [
            'site_key'   => 'mautic.integration.turnstile.site_key',
            'secret_key' => 'mautic.integration.turnstile.secret_key',
        ];
    }

    /**
     * @param FormBuilder|Form $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea === 'keys') {
            $builder->add(
                'mode',
                ChoiceType::class,
                [
                    'choices' => [
                        'mautic.turnstile.managed'         => 'managed',
                        'mautic.turnstile.non_interactive'  => 'non_interactive',
                        'mautic.turnstile.invisible'        => 'invisible',
                    ],
                    'label'      => 'mautic.turnstile.mode',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                    ],
                    'required'    => false,
                    'placeholder' => false,
                    'data'=> isset($data['mode']) ? $data['mode'] : 'managed'
                ]
            );
        }
    }
}
