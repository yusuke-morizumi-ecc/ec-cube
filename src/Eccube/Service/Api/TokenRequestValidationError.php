<?php
namespace Eccube\Service\Api;

/**
 * https://datatracker.ietf.org/doc/html/rfc6749#section-5.2
 */
enum TokenRequestValidationError
{
    case InvalidRequest;

    case InvalidClient;

    case InvalidGrant;  // このエラーを返すパターンはない

    case UnauthorizedClient;  // このエラーを返すパターンはない

    case UnsupportedGrantType;  // このエラーを返すパターンはない

    case InvalidScope;  // このエラーを返すパターンはない

    public function toString():string
    {
        return match($this) {
            TokenRequestValidationError::InvalidRequest => 'invalid_request',
            TokenRequestValidationError::InvalidClient => 'invalid_client',
            // TokenRequestValidationError::InvalidGrant => 'invalid_grant',
            // TokenRequestValidationError::UnauthorizedClient => 'unauthorized_client',
            // TokenRequestValidationError::UnsupportedGrantType => 'unsupported_grant_type',
            // TokenRequestValidationError::InvalidScope => 'invalid_scope',
        };
    }
}
