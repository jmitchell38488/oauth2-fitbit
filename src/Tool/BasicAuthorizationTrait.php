<?php
/**
 * This file is part of the jmitchell38488/oauth2-fitbit library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Justin Mitchell <jmitchell38488@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link https://packagist.org/packages/Jmitchell38488/oauth2-fitbit Packagist
 * @link https://github.com/jmitchell38488/oauth2-fitbit GitHub
 */

namespace Jmitchell38488\OAuth2\Client\Tool;

/**
 * Enables `Basic` header authorization for providers. This is required for the 
 * FitBit API
 * 
 * @link https://dev.fitbit.com/docs/oauth2/#obtaining-consent Obtaining Consent
 * @author Justin Mitchell <jmitchell38488@gmail.com>
 * @since 0.1
 * @package Jmitchell38488\OAuth2\Client\Tool
 */
trait BasicAuthorizationTrait
{
    /**
     * <p>Returns authorization headers for the 'basic' grant. The token is 
     * created by base 64 encoding a concatenated string, by combining the 
     * FitBit API Client ID and Client Secret together, separated by a colon.</p>
     * <p><b>Example:</b>
     * 
     * <pre><code>
     * Token: base64_encode(sprintf('%s:%s', $client_id, $client_secret))
     * Return: Y2xpZW50X2lkOmNsaWVudCBzZWNyZXQ=
     * 
     * POST https://api.fitbit.com/oauth2/token
     * Authorization: Basic Y2xpZW50X2lkOmNsaWVudCBzZWNyZXQ=
     * Content-Type: application/x-www-form-urlencoded
     * 
     * client_id=22942C&grant_type=authorization_code&redirect_uri=http%3A%2F%2Fexample.com%2Fcallback&code=1234567890
     * </code></pre>
     *
     * @param  mixed|null $token Either a string or an access token instance
     * @return array
     * @link https://dev.fitbit.com/docs/oauth2/#access-token-request Making a Token Request
     */
    protected function getAuthorizationHeaders($token = null)
    {   
        return ['Authorization' => 'Basic ' . $token];
    }
}
