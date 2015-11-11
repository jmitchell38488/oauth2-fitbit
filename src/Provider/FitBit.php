<?php

namespace Healthand\OAuth2\Client\Provider;

use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

/**
 * Represents a FitBit request service provider that's based on the authorization
 * grant flow and may be used to interact with the OAuth 2.0 service provider, 
 * using Bearer token authentication
 */
class FitBit extends FitBitAuthorization
{
    use BearerAuthorizationTrait;

    /**
     * @inheritdoc
     */
    public function __construct(array $options = [], array $collaborators = [])
    {   
        parent::__construct($options, $collaborators);
    }

}