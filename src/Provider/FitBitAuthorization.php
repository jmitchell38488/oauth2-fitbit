<?php

namespace Healthand\OAuth2\Client\Provider;

use Healthand\OAuth2\Client\Tool\BasicAuthorizationTrait;
use InvalidArgumentException;

/**
 * Represents a FitBit Authorization Grant Flow service provider that may be 
 * used to interact with the OAuth 2.0 service provider, using Basic token
 * authentication
 */
class FitBitAuthorization extends AbstractFitBit
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
        
        return $poptions;
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken($grant, array $options = [])
    {
        $grant = $this->verifyGrant($grant);

        $params = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
        ];
        
        $token = null;
        if (!empty($options['token'])) {
            $token = $options['token'];
            unset($options['token']);
        }

        $params   = $grant->prepareRequestParameters($params, $options);
        $request  = $this->getAccessTokenRequest($params, $token);
        $response = $this->getResponse($request);
        $prepared = $this->prepareAccessTokenResponse($response);
        $token    = $this->createAccessToken($prepared, $grant);

        return $token;
    }

}