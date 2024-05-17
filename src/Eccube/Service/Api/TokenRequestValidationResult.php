<?php

namespace Eccube\Service\Api;

class TokenRequestValidationResult
{
    private bool $valid;

    private ?TokenRequestValidationError $error;

    private ?string $errorDescription;

    private function __construct(bool $valid, TokenRequestValidationError $error = null, string $errorDescription = null)
    {
        $this->valid = $valid;
        $this->error = $error;
        $this->errorDescription = $errorDescription;
    }

    public static function validResult(): TokenRequestValidationResult
    {
        return new TokenRequestValidationResult(true);
    }

    public static function invalidResult(TokenRequestValidationError $error, string $errorDescription): TokenRequestValidationResult
    {
        return new TokenRequestValidationResult(false, $error, $errorDescription);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getError(): ?TokenRequestValidationError
    {
        return $this->error;
    }

    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }
}
