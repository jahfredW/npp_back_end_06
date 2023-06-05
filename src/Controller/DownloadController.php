<?php

namespace App\Controller;


use Imagine\Image\Box;
use App\Entity\Picture;
use Imagine\Image\Point;
use GuzzleHttp\Psr7\Stream;
use Imagine\Imagick\Imagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Google\Cloud\Storage\StorageClient;
use App\Services\Storage\GoogleCloudStorage;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Factory\GoogleCloudStorageServiceFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class DownloadController extends AbstractController {

    #[Route('/api/downloadFile/{id}', name: 'download', methods: ['GET'])]
    public function upload($id, Picture $picture, Request $request)
    {

    $bucketName = 'npp_photos_prod';
    
    $objectName = $picture->getFileName();
    
    $storage = GoogleCloudStorage::getInstance($bucketName);
    $storage->setObjectName($objectName);
   
    $object = $stream = $storage->getObject();

    $stream = $storage->getObject()->downloadAsStream();

    $response = new StreamedResponse();

    $response->setCallback(function() use ($stream) {
        fpassthru($stream->detach());
    });

    $response->headers->set('Content-Type', 'image/jpeg');
    $response->headers->set('Content-Disposition', 'attachment; filename="ma_photo.jpg"');
   
    

    return $response;

    }

    #[Route('/api/downloadLink', name: 'downloadLink', methods: ['GET'])]
    public function uploaded(Request $request)
    {
    
    
    // Get the bucket name and object name
    $bucketName = 'npp_photos_prod';
    $objectName = 'c1341feb606a398e5604904743ced160.jpg';

    $storage = GoogleCloudStorage::getInstance($bucketName);
    $storage->setObjectName($objectName);

    // Create a signed URL with a short expiration time (5 minutes)
    // Create a signed URL with a short expiration time (5 minutes)
    $content = $storage->getObject()
         ->signedUrl(new \DateTime('+5 minutes'), [
             'version' => 'v4',
             'private' => true,
         ]);


         return new Response($content);

    


    }

    #[Route('/api/thumbnail', name: 'thumbnail', methods: ['GET'])]
    public function thumbnail(Request $request)
    {
    
    
    // Get the bucket name and object name
    $bucketName = 'npp_photos_prod';
    $objectName = '078b4a1ef6311bc7cba2ff8b30de8492.jpg';
    $thumbnailWidth = 100;
    $thumbnailHeight = 100;
    $validityDuration = 300; // 5 minutes

    $storage = GoogleCloudStorage::getInstance($bucketName);
    $storage->setObjectName($objectName);

    // Create a signed URL with a short expiration time (5 minutes)
    // Create a signed URL with a short expiration time (5 minutes)
    $content = $storage->getObject()
         ->signedUrl(new \DateTime('+'.$validityDuration.' seconds'), [
             'version' => 'v4',
             'private' => true,
         ]);

    // création du thumbnail 
    $imagine = new Imagine();
    $sourceImage = $imagine->read($url);
    $thumbnailImage = $sourceImage->thumbnail(new Box($thumbnailWidth, $thumbnailHeight), ImageInterface::THUMBNAIL_OUTBOUND);

    $imageData = $thumbnailImage->get('jpg');

    $reponse = new Response();
    $response->headers->set('Content-Type', 'image/jpeg');
    $response->headers->set('Content-Disposition', 'attachment; filename="thumbnail.jpg"');
    $response->setContent($imageData);

    return $response;

    


    }

    

   
}
