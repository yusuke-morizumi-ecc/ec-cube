<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TopController extends AbstractController
{
    /**
     * @Route("/", name="dummy_homepage", methods={"GET"})
     */
    public function dummyIndex(Request $request)
    {
        $locale = $request->getLocale() ?? env('ECCUBE_LOCALE');
        return $this->redirectToRoute('homepage', ['_locale' => $locale]);
    }

    /**
     * @Route("/{_locale<%app.supported_locales%>}", name="homepage", methods={"GET"})
     * @Template("index.twig")
     */
    public function index()
    {
        return [];
    }
}
