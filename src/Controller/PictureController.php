<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Storage\GoogleCloudStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\ContextAwarePaginatorInterface;

class PictureController extends AbstractController
{
    private $em;
    private $cs;
    const BUCKET_NAME = 'npp_photos_prod';


    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
        $this->cs = GoogleCloudStorage::getInstance(PictureController::BUCKET_NAME);
    }


    #[Route('/api/picture/{id}', name: 'app_picture', methods: ['GET'])]
    public function getPictures(Request $request ): JsonResponse
    {
    $id = $request->get('id');

    $picture = $this->em
        ->getRepository(Picture::class)->find($id);

    if ($picture) {
        $pictureFileName = $picture->getThumbnail();
        $pictureName = $picture->getName();
        $this->cs->setObjectName($pictureFileName);
        $url = $this->cs->getObject()
                ->signedUrl(new DateTime('+24 hours'), [
                    'version' => 'v4',
                    'private' => true,
                ]);
                
    return new JsonResponse($url);

    }

    throw new NotFoundHttpException('L\'entité demandée n\'existe pas.');
    
    }


    #[Route('/api/picture/{id}/name', name: 'app_picture_name', methods: ['GET'])]
    public function getPictureName(Request $request ): JsonResponse
    {
    $id = $request->get('id');

    $picture = $this->em
        ->getRepository(Picture::class)->find($id);

    if ($picture) {
        $pictureName = $picture->getName();
        
                
    return new JsonResponse($pictureName);

    }

    throw new NotFoundHttpException('L\'entité demandée n\'existe pas.');
    
    }

    #[Route('/api/picture/{id}/type', name: 'app_picture_type', methods: ['GET'])]
    public function getPictureType(Request $request ): JsonResponse
    {
    $id = $request->get('id');

    $picture = $this->em
        ->getRepository(Picture::class)->find($id);
    
    if ($picture) {
        $pictureType = $picture->getProduct()->getId();
        
                
    return new JsonResponse($pictureType);

    }

    throw new NotFoundHttpException('L\'entité demandée n\'existe pas.');
    
    }
    // récupération des photos de carousel 
    #[Route('/api/pictures', name: 'app_picture_carousel', methods: ['GET'])]
    public function getPicturesCarousel(Request $request ): JsonResponse
    {

    $picturesCarouselContainer = [];

    $isCarousel = $request->query->get('isCarousel');
  

    $pictures = $this->em
        ->getRepository(Picture::class)->findByIsCarousel($isCarousel);

    
    if ($pictures != null) {
        
        foreach($pictures as $picture){
            $pictureFileName = $picture->getFileName();
            
            $this->cs->setObjectName($pictureFileName);
            $url = $this->cs->getObject()
                    ->signedUrl(new DateTime('+24 hours'), [
                        'version' => 'v4',
                        'private' => true,
                    ]);
            
            // $fileContents = file_get_contents($url);
            // dd($fileContents);
            $picturesCarouselContainer[] = $url;
        }
        
    
                

    }
    return new JsonResponse($picturesCarouselContainer);

    
    
    }


    // Delete a picture : attention : delete cascade à desactiver ici ! 
    #[Route('/api/picture/{id}', name: 'delete_picture', methods: ['DELETE'])]
    public function deletePicture($id, Picture $picture, Request $request ): JsonResponse
    {
    $id = $request->get('id');

    $picture = $this->em
        ->getRepository(Picture::class)->find($id);
   
    if($picture){
        try{
            $storage = GoogleCloudStorage::getInstance(PictureController::BUCKET_NAME);
        $pictureFileName = $picture->getFileName();
        $storage->setObjectName($pictureFileName);
        $storage->getObject()->delete();
        } catch( Exception $e ) {
            throw new NotFoundHttpException($e->getMessage());
        }
        

        $this->em->remove($picture);
        $this->em->flush();

        return new JsonResponse('ok');
    }
    
                
    
    throw new NotFoundHttpException('L\'entité demandée n\'existe pas.');

    }

    // update One Picture isActive
#[Route("/api/picture/{id}/isactive", name: "picture_update", methods:['PUT'])]
public function updatePictureActive($id, Picture $picture, Request $request ) : Response
{
   
    $content = $request->toArray();

    $published = $content['published'];

    !$published ? $picture->setIsActive(false) : $picture->setIsActive(true); 
    
    $this->em->persist($picture);
    $this->em->flush();
  

    return new Response('updated active');
}

// update One Picture isCarousel 
#[Route("/api/picture/{id}/iscarousel", name: "picture_update", methods:['PUT'])]
public function updatePictureCarousel($id, Picture $picture, Request $request ) : JsonResponse
{
   
    // récup sous forme d'array
    $content = $request->toArray();

    $published = $content['carousel'];

    // récupérer l'état de la picture 
    $pictureStateCarousel = $picture->isIsCarousel();
   
    // changement du status 
    !$published ? $picture->setIsCarousel(false) : $picture->setIsCarousel(true); 

    // vérification du nombre d'images de couverture 
    $isFull = $this->em->getRepository(Picture::class)->findTotalIsCarousel(1);

    if($isFull < 3 || ($pictureStateCarousel & $isFull == 3)  ){
        $this->em->persist($picture);
        $this->em->flush();

        return new JsonResponse('updated carousel', Response::HTTP_NO_CONTENT) ;
    } 

    return new JsonResponse('nombre maximal de photos atteint', Response::HTTP_OK);


    
    
  

    
}

    


}  

