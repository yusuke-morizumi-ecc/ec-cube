<?php


namespace Eccube\Controller;

use Eccube\Entity\Api\AccessToken;
use Eccube\Repository\Api\AccessTokenRepository;
use Eccube\Service\Api\TokenRequestValidator;
use Eccube\Service\Api\TokenRequestValidationError;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Uid\Uuid;

class ApiController extends AbstractController
{
    private AccessTokenRepository $accessTokenRepository;

    public function __construct(AccessTokenRepository $accessTokenRepository)
    {
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * アクセストークンを返す
     *
     * @Route("/api/token", name="get_token", methods={"POST"})
     */
    public function token(Request $request)
    {
        $validator = new TokenRequestValidator();
        $validationResult = $validator->validate($request, "clientId", "clientSecret");

        if (!$validationResult->isValid()) {
            $error = $validationResult->getError();
            $httpStatusCode = match($error) {
                TokenRequestValidationError::InvalidRequest => 400,
                TokenRequestValidationError::InvalidClient => 401,
            };
            $response = ["error" => $error->toString(), "error_description" => $validationResult->getErrorDescription()];
            return new JsonResponse($response, $httpStatusCode);
        }

        $token = Uuid::v4();
        $accessToken = new AccessToken();
        $accessToken->setAccessToken(password_hash($token,PASSWORD_BCRYPT))
                ->setCreateDate(new \DateTime())
                ->setExpireDate((new \DateTime())->modify("+ 3600 sec"));
        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();
        $httpStatusCode = 200;
        $response = ["access_token" => $token, "token_type" => "bearer", "expires_in" => 3600];

        return new JsonResponse($response, $httpStatusCode);
    }


}
