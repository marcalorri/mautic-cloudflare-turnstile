<?php

$defaultInputClass = (isset($inputClass)) ? $inputClass : 'input';
$containerType     = 'div-wrapper';

include __DIR__.'/../../../../app/bundles/FormBundle/Views/Field/field_helper.php';

$action   = $app->getRequest()->get('objectAction');
$settings = $field['properties'];

$formName    = str_replace('_', '', $formName);
$hashedFormName = md5($formName);
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

$siteKey = $field['customParameters']['site_key'];
$mode    = isset($field['customParameters']['mode']) ? $field['customParameters']['mode'] : 'managed';
$alias   = $field['alias'];

if ($mode === 'invisible') {
    $jsElement = <<<JSELEMENT
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileLoad_{$hashedFormName}" async defer></script>
    <script type="text/javascript">
    function onTurnstileLoad_{$hashedFormName}() {
        turnstile.render('#turnstile-container_{$hashedFormName}', {
            sitekey: '{$siteKey}',
            callback: function(token) {
                document.getElementById("mauticform_input_{$formName}_{$alias}").value = token;
            }
        });
    }
    </script>
JSELEMENT;

    $widgetHtml = <<<HTML
<div id="turnstile-container_{$hashedFormName}"></div>
HTML;
} else {
    $jsElement = <<<JSELEMENT
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script type="text/javascript">
    function verifyCallback_{$hashedFormName}(response) {
        document.getElementById("mauticform_input_{$formName}_{$alias}").value = response;
    }
    </script>
JSELEMENT;

    $widgetHtml = <<<HTML
<div class="cf-turnstile" data-sitekey="{$siteKey}" data-callback="verifyCallback_{$hashedFormName}"></div>
HTML;
}

$html = <<<HTML
    {$jsElement}
	<div $containerAttr>
        {$label}
        {$widgetHtml}
        <input $inputAttr type="hidden">
        <span class="mauticform-errormsg" style="display: none;"></span>
    </div>
HTML;
?>

<?php
echo $html;
?>
