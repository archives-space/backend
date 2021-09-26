<?php

namespace App\Controller;

use App\Document\Catalog\Picture;
use App\Manager\Catalog\PictureManager;
use App\Utils\Catalog\PictureHelpers;
use App\Utils\FileManager;
use PHPExif\Reader\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
            'path' => 'src/Controller/DefaultController.php',
        ]);
    }

    /**
     * Création de la route "image to base64"
     * @Route("/image-to-base-64", name="IMAGE_TO_BASE_64", methods={"GET"})
     */
    public function imageToBase64(KernelInterface $kernel)
    {
        $path = $kernel->getProjectDir() . '/var/image0.jpeg';
        $path = $kernel->getProjectDir() . '/var/5eUO24bIIqY.jpg';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $this->json([
            'img' => $base64,
        ]);
    }

    /**
     * @Route("/object-storage", name="TEST_OBJECT_STORAGE", methods={"POST"})
     * @param RequestStack $requestStack
     * @param FileManager $fileManager
     */
    public function testObjectStorage(RequestStack $requestStack, FileManager $fileManager)
    {
        $file = $fileManager->parse($requestStack);
        $reader = Reader::factory(Reader::TYPE_NATIVE);
        $exifData = $reader->read($file->getUploadedFile()->getRealPath());
        dd($exifData);
    }

}



