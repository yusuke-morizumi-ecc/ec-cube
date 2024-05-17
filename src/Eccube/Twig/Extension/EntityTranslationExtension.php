<?php

namespace Eccube\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Eccube\Entity\Translatable;

class EntityTranslationExtension extends AbstractExtension {

    private RequestStack $requestStack;

    /**
     * constructor
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[] An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('trans_entity', [$this, 'translateEntity']),
        ];
    }


    public function translateEntity(Translatable $entity, $propertyName): string {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            $locale = env('ECCUBE_LOCALE');
        } else {
            $locale = $request->getLocale() ?? env('ECCUBE_LOCALE');
        }

        dump($locale);

        return $entity->getTranslatedProperty($propertyName, $locale);
    }
}
