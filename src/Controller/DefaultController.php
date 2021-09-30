<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 * @Route(defaults={"_format"="json"})
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path'    => 'src/Controller/DefaultController.php',
        ]);
    }

    /**
     * Création de la route "image to base64"
     * @Route("/image-to-base-64", name="IMAGE_TO_BASE_64", methods={"POST"})
     */
    public function imageToBase64(Request $request, KernelInterface $kernel)
    {
        /** @var UploadedFile $uplaodedFile */
        if(!$uploadedFile = $request->files->get('file')){
            return $this->json([
                'img' => '',
            ]);
        }

//        $path   = $kernel->getProjectDir() . '/var/5eUO24bIIqY.jpg';
        $path   = $uploadedFile->getRealPath();
        $type   = pathinfo($path, PATHINFO_EXTENSION);
        $data   = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $this->json(base64_encode(file_get_contents($path)));
        return $this->json($base64);
    }
}



