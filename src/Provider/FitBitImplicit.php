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

namespace Jmitchell38488\OAuth2\Client\Provider;

use Jmitchell38488\OAuth2\Client\Tool\BasicAuthorizationTrait;
use InvalidArgumentException;

/**
 * <p>Represents a FitBit OAuth 2.0 service provider.</p>
 * <p>This class represents the 
 * Implicit grant flow for FitBit API. Whereas Authorization grant flow requires
 * 4 steps and returns the authorize response as query parameters, the Implicit
 * grant flow requires 3 steps, skipping the token step, and returns the
 * authorize request as a URI fragment rather than query string. A front-end
 * application will need to send the fragment to the server if intending to use
 * for multiple requests.</p>
 *
 * <p>Once a user session is authorized and the access token is returned from
 * the API, you should use the <strong>FitBit</strong> class to make API requests
 * as it uses the <em>Bearer</em> Authorization token, rather than the
 * <em>Basic</em> Authorization token.</p>.
 * 
 * @link https://dev.fitbit.com/docs/oauth2/#implicit-grant-flow Obtaining Consent
 * @author Justin Mitchell <jmitchell38488@gmail.com>
 * @since 0.1
 * @package Jmitchell38488\OAuth2\Client\Provider
 */
class FitBitImplicit extends AbstractFitBit
{
    use BasicAuthorizationTrait;

    /**
     * <p>Returns authorization parameters based on provided options. FitBit 
     * implicit grant flow differs from the generic authorization grant
     * flow in several ways. First, the prompt field is <em>prompt</em> instead of
     * <em>approval_prompt</em>, and the default library will always insert the request
     * field <em>approval_prompt</em>. Second, a scope is required and there is a
     * minimum set of values and a total set of values. If there are any scope
     * errors the API will return an error. Lastly, an <em>expires_in</em> field
     * is required to set the token expiry. The user can override this on the
     * API if the request prompt is set to <em>consent</em></p>
     * 
     * <p><b>Example:</b><br />
     * https://www.fitbit.com/oauth2/authorize?response_type=token&client_id=22942C&redirect_uri=http%3A%2F%2Fexample.com%2Fcallback&scope=activity%20nutrition%20heartrate%20location%20nutrition%20profile%20settings%20sleep%20social%20weight&expires_in=604800
     * </p>
     *
     * @param  array $options
     * @return array Authorization parameters
     * @link https://dev.fitbit.com/docs/oauth2/#authorization-page Authorization
     * @link https://dev.fitbit.com/docs/oauth2/#scope FitBit API Scope
     */
    protected function getAuthorizationParameters(array $options)
    {
        // Check for required scopes
        if (!empty($options['scope']) && count($options['scope']) < count($this->defaultScopes)) {
            throw new InvalidArgumentException("Could not configure provider, missing required scopes");
        }
        
        // If scopes provided but not all required are provided
        if (count(array_intersect($options['scope'], $this->defaultScopes)) != count($this->defaultScopes)) {
            throw new InvalidArgumentException("Could not configure provider, missing required scopes");
        }
        
        // Invalid scope provided
        if (count($options['scope']) - count(array_intersect($options['scope'], $this->allScopes)) != 0) {
            throw new InvalidArgumentException("Could not configure provider, invalid scope(s) provided");
        }
        
        if (empty($options['scope'])) {
            $options['scope'] = $this->defaultScopes;
        }
        
        $poptions = parent::getAuthorizationParameters($options);
        
        // option[approval_prompt] needs to be remapped to [prompt]
        unset($poptions['approval_prompt']);
        if (isset($options['prompt'])) {
            $poptions['prompt'] = $options['prompt'];
        }
        
        // Set the expires query part, if it is not provided, set to 1 day by default
        $poptions['expires_in'] = isset($options['expires_in']) ? $options['expires_in'] : AbstractFitBit::EXPIRES_IN_DAY;
        
        return $poptions;
    }

}