<?php

namespace Healthand\OAuth2\Client\Provider;

use Healthand\OAuth2\Client\Tool\BasicAuthorizationTrait;
use InvalidArgumentException;

/**
 * Represents a FitBit Implicit Grant Flow service provider that may be 
 * used to interact with the OAuth 2.0 service provider, using Basic token
 * authentication
 */
class FitBitImplicit extends AbstractFitBit
{
    use BasicAuthorizationTrait;

    /**
     * @inheritdoc
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