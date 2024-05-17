<?php


namespace Eccube\Controller;

use Eccube\Repository\ProductRepository;
use Eccube\Service\Api\TokenRequestValidator;
use Eccube\Service\Api\TokenRequestValidationError;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
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

        if ($validationResult->isValid()) {
            $httpStatusCode = 200;
            $response = ["access_token" => "xxxxxxxxxxxxx", "token_type" => "bearer", "expires_in" => 3600];
        } else {
            $error = $validationResult->getError();
            $httpStatusCode = match($error) {
                TokenRequestValidationError::InvalidRequest => 400,
                TokenRequestValidationError::InvalidClient => 401,
            };
            $response = ["error" => $error->toString(), "error_description" => $validationResult->getErrorDescription()];
        }

        return new JsonResponse($response, $httpStatusCode);
    }


}
