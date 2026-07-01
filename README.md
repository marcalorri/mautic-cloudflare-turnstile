# Mautic Cloudflare Turnstile Plugin

## Credits & Attribution

This plugin is a derivative work based on the original [Mautic reCAPTCHA Plugin](https://github.com/KonstantinCodes/mautic-recaptcha) by [Konstantin Scheumann](https://github.com/KonstantinCodes).

The original plugin has been adapted to replace Google reCAPTCHA functionality with Cloudflare Turnstile.

Copyright for the original plugin architecture and design remains with its original author. Modifications for the Cloudflare Turnstile integration are maintained in this project.

This project is licensed under the GNU General Public License v3.0. See the [LICENSE](LICENSE) file for the full license text.

This Plugin brings Cloudflare Turnstile integration to Mautic 4.

## Installation via .zip
Download the .zip file, extract it into the `plugins/` directory and rename the new directory to `MauticTurnstileBundle`.

Clear the cache via console command `php bin/console cache:clear --env=prod` (might take a while) *OR* manually delete the `var/cache/prod` directory.

## Configuration
Navigate to the Plugins page and click "Install/Upgrade Plugins". You should now see a "Cloudflare Turnstile" plugin. Open it to configure your Site Key and Secret Key from your [Cloudflare Turnstile dashboard](https://dash.cloudflare.com/?to=/:account/turnstile).

You can also select the widget mode:
- **Managed (recommended)**: Cloudflare automatically decides whether to show an interactive challenge based on visitor risk level.
- **Non-Interactive**: Visitors see a loading spinner but are never required to interact.
- **Invisible**: No visible widget. Challenges run entirely in the background.

> **Note**: When using Invisible mode, you must reference Cloudflare's [Turnstile Privacy Addendum](https://www.cloudflare.com/turnstile-privacy-policy/) in your privacy policy.

## Usage in Mautic Form
Add the "Cloudflare Turnstile" field to your form and save changes. The widget will appear on the public form page and validate submissions server-side.
