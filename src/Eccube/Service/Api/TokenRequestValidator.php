<?php

namespace Eccube\Service\Api;

use Eccube\Service\Api\TokenRequestValidationResult;
use Symfony\Component\HttpFoundation\Request;

class TokenRequestValidator
{

    /**
     * トークン取得リクエストのバリデーション
     *
     * @param Request $request
     * @param string $cliendId
     * @param string $cliendSecret
     * @return TokenRequestValidationResult
     */
    public function validate(Request $request, string $cliendId, string $cliendSecret): TokenRequestValidationResult
    {
        $grantType = $request->get('grant_type');

        $authorizationHeader = $request->headers->get('Authorization');

        if ($grantType === null || $grantType != 'client_credentials' || empty($authorizationHeader)) {
            if ($grantType === null) {
                $errorDescription = "The request is missing the 'grant_type' parameter";
            } elseif ($grantType != 'client_credentials') {
                $errorDescription = "The 'grant_type' parameter is not supported";
            } else {
                $errorDescription = "The request is missing the 'Authorization' header";
            }

            return TokenRequestValidationResult::invalidResult(TokenRequestValidationError::InvalidRequest, $errorDescription);
        }

        $expectedAuthorizationHeader = $this->getExpectedAuthorizationHeader($cliendId, $cliendSecret);

        if ($expectedAuthorizationHeader != $authorizationHeader) {
            return TokenRequestValidationResult::invalidResult(TokenRequestValidationError::InvalidClient, "Client authentication failed");
        }

        return TokenRequestValidationResult::validResult();
    }

    /**
     * @param string $cliendId
     * @param string $cliendSecret
     * @return string
     */
    private function getExpectedAuthorizationHeader(string $cliendId, string $cliendSecret): string
    {
        return "Basic " . base64_encode($cliendId . ":" . $cliendSecret);
    }
}
