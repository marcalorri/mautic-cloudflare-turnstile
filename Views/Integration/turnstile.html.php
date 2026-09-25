<?php

$defaultInputClass = (isset($inputClass)) ? $inputClass : 'input';
$containerType     = 'div-wrapper';

include __DIR__.'/../../../../app/bundles/FormBundle/Views/Field/field_helper.php';

$action   = $app->getRequest()->get('objectAction');
$settings = $field['properties'];

$formName    = str_replace('_', '', $formName);
$formButtons = (!empty($inForm)) ? $view->render(
    'MauticFormBundle:Builder:actions.html.php',
    [
        'deleted'        => false,
        'id'             => $id,
        'formId'         => $formId,
        'formName'       => $formName,
        'disallowDelete' => false,
    ]
) : '';

$label = (!$field['showLabel'])
    ? ''
    : <<<HTML
<label $labelAttr>{$view->escape($field['label'])}</label>
HTML;

$siteKey = $view->escape($field['customParameters']['site_key']);

$html = <<<HTML
    <script type="text/javascript">
    (function () {
        if (window.MauticTurnstile) {
            window.MauticTurnstile.scan(document);
            return;
        }

        function resetForm(formName) {
            var forms = document.querySelectorAll('form[data-mautic-form]');
            for (var i = 0; i < forms.length; i++) {
                if (forms[i].getAttribute('data-mautic-form') !== formName) {
                    continue;
                }
                var widgets = forms[i].querySelectorAll('[data-mautic-turnstile]');
                for (var j = 0; j < widgets.length; j++) {
                    if (widgets[j].mauticTurnstileInput) {
                        widgets[j].mauticTurnstileInput.value = '';
                    }
                    if (widgets[j].mauticTurnstileId != null) {
                        window.turnstile.reset(widgets[j].mauticTurnstileId);
                    }
                }
            }
        }

        function registerForm(form) {
            var formName = form && form.getAttribute('data-mautic-form');
            if (!formName) {
                return;
            }
            window.MauticFormCallback = window.MauticFormCallback || {};
            var callbacks = window.MauticFormCallback[formName] || {};
            if (callbacks.mauticTurnstileInstalled) {
                return;
            }
            var previous = callbacks.onResponse;
            callbacks.onResponse = function (response) {
                resetForm(formName);
                return typeof previous === 'function' ? previous.apply(this, arguments) : undefined;
            };
            callbacks.mauticTurnstileInstalled = true;
            window.MauticFormCallback[formName] = callbacks;
        }

        function render(element) {
            if (element.mauticTurnstileRendered) {
                return;
            }
            var input = element.nextElementSibling;
            if (!input || input.type !== 'hidden') {
                return;
            }
            element.mauticTurnstileRendered = true;
            element.mauticTurnstileInput = input;
            element.mauticTurnstileId = window.turnstile.render(element, {
                sitekey: element.getAttribute('data-sitekey'),
                'response-field': false,
                callback: function (token) {
                    input.value = token;
                },
                'expired-callback': function () {
                    input.value = '';
                },
                'error-callback': function () {
                    input.value = '';
                },
                'timeout-callback': function () {
                    input.value = '';
                }
            });
            registerForm(element.closest('form'));
        }

        function scan(root) {
            if (!window.turnstile) {
                return;
            }
            if (root.nodeType === 1 && root.matches('[data-mautic-turnstile]')) {
                render(root);
            }
            if (root.querySelectorAll) {
                var widgets = root.querySelectorAll('[data-mautic-turnstile]');
                for (var i = 0; i < widgets.length; i++) {
                    render(widgets[i]);
                }
            }
        }

        window.MauticTurnstile = {scan: scan};
        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                for (var j = 0; j < mutations[i].addedNodes.length; j++) {
                    scan(mutations[i].addedNodes[j]);
                }
            }
        }).observe(document.documentElement, {childList: true, subtree: true});

        var script = document.querySelector('script[src^="https://challenges.cloudflare.com/turnstile/v0/api.js"]');
        if (!script && !window.turnstile) {
            script = document.createElement('script');
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
            script.async = true;
            document.head.appendChild(script);
        }
        if (script) {
            script.addEventListener('load', function () { scan(document); });
        }
        scan(document);
    })();
    </script>
    <div $containerAttr>
        {$label}
        <div data-mautic-turnstile data-sitekey="{$siteKey}"></div>
        <input $inputAttr type="hidden">
        <span class="mauticform-errormsg" style="display: none;"></span>
    </div>
HTML;
?>

<?php
echo $html;
?>
