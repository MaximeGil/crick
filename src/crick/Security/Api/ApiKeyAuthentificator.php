<?php

nameSpace crick\Security\Api;

use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ApiKeyAuthentificator implements SimplePreAuthenticatorInterface {

    public function createToken(Request $request, $providerKey) {
        // look for an apikey query parameter
        $apiKey = $request->query->get('apikey');
        // or if you want to use an "apikey" header, then do something like this:
        // $apiKey = $request->headers->get('apikey');
        if (!$apiKey) {
            throw new BadCredentialsException('No API key found');
            // or to just skip api key authentication
            // return null;
        }
        return new PreAuthenticatedToken(
                'anon.', $apiKey, $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey) {
        if (!$userProvider instanceof Provider\ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
            sprintf(
                    'The user provider must be an instance of ApiKeyUserProvider (%s was given).', get_class($userProvider)
            )
            );
        }
        $apiKey = $token->getCredentials();
        $username = $userProvider->getUsernameForApiKey($apiKey);
        if (!$username) {
            throw new AuthenticationException(
            sprintf('API Key "%s" does not exist.', $apiKey)
            );
        }
        $user = $userProvider->loadUserByUsername($username);
        return new PreAuthenticatedToken(
                $user, $apiKey, $providerKey, $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey) {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

}
