<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Utils\Utils;
use App\Entity\Album;
use DateTimeImmutable;
use App\Entity\Picture;
use App\Entity\Category;
use App\Entity\Products;
use Symfony\Component\Finder\Finder;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Optimizer\ImageOptimizer;
use App\Services\Storage\GoogleCloudStorage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlbumController extends AbstractController
{
    private $em;
    private $serializer;
    private $cs;
    const BUCKET_NAME = 'npp_photos_prod';
    private $imgOptimizer;
    const MAX_FILE_SIZE = 20000000;


    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, ImageOptimizer $imgOptimizer){
        $this->em = $em;
        $this->serializer = $serializer;
        $this->cs = GoogleCloudStorage::getInstance(AlbumController::BUCKET_NAME);
        $this->imgOptimizer = $imgOptimizer;
        
    }

    #[Route('/api/album', name: 'app_albums', methods:['GET'])]
    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function getAllAlbums(Request $request): Response
    {
        // récuépration du query param page - default 1
        $page = $request->query->getInt('page', 1);
        // récupération du query param limit défaut 10
        $limit = $request->query->getInt('limit', 10);
        // récupération de la date de début 
        $beginDate = $request->query->get('beginDate');
        $endDate = $request->query->get('endDate');

        // récupération des catégories et types d'albums
        $albumCategory = $request->query->getInt('category');
        $albumType = $request->query->getInt('type');
        $albumName = $request->query->get('albumName');
       

        // dd($albumCategory);
       
        // récupération des date de début et dates de fin : 
        $beginDate = $request->query->get('beginDate');
        $endDate = $request->query->get('endDate');

        // utilisation de la méthode de classe dateDecoder pour convertir les dates 
        $beginDateObject = Utils::dateDecoder($beginDate);
        $endDateObject = Utils::dateDecoder($endDate);

        if( $beginDateObject > $endDateObject && $endDateObject != null){
            throw new HttpException(404, 'La date de fin ne peut être supérieure');
        }

        // if( $albumName){
        //     throw new HttpException(404, $albumName);
        // }
        // $decodedBeginDate = urldecode($beginDate);
        // $sliceDate = substr($decodedBeginDate, 0 , (strpos( $decodedBeginDate, 'G') - 1) );
        // // dd($decodedBeginDate);
        // // dd($sliceDate);

        // $beginDateObject = DateTimeImmutable::createFromFormat('D M d Y H:i:s', $sliceDate);

    
        // if (isset($albumCategory) && !is_null($albumCategory)) {
        //     dd(gettype($albumCategory));
        //     $albumCategory = $request->query->get('albumCategory');
        // }
    
        // if (!empty($request->query->get('albumType'))) {
        //     $albumType = $request->query->get('albumType');
        // }
         
        // dd($albumCategory, $albumType);


        

        // offset = reprise : page - 1 * la limite : 
        // page 1 : offset 0,
        // page2 : offset 10 -> on charge à partir de 11 etc etc 
        $offset = ($page - 1) * $limit;

        $albumRepository = $this->em->getRepository(Album::class);
        $albumList = $albumRepository->findAlbumWithPagination($limit, $offset, $albumCategory, $albumType,
        $beginDateObject, $endDateObject, $albumName);



        foreach ( $albumList as $album) {
            $coverPictureName = $album->getCoverPicture();
            // $storage = GoogleCloudStorage::getInstance(AlbumController::BUCKET_NAME);
            $this->cs->setObjectName($coverPictureName);
            $url = $this->cs->getObject()
                ->signedUrl(new DateTime('+30 minutes'), [
                    'version' => 'v4',
                    'private' => true,
                ]);
                $album->setCoverPicture($url);
        }
        
        // $user = $this->getUser();
        // dd($user->getId());       
        $jsonSessionList = $this->serializer->serialize($albumList, 'json', ['groups' => 'getAlbums']);
        
        
        return new JsonResponse($jsonSessionList, Response::HTTP_OK, [], true);
    }

    // #[Route('/api/album/coverPicture', name: 'cover_session', methods:['GET'])]
    // public function getCoverPicture(): JsonResponse
    // {
    //     $sessionRepository = $this->em->getRepository(Album::class);
    //     $sessionList = $sessionRepository->findAll();

    //     $jsonSessionList = $this->serializer->serialize($sessionList, 'json', ['groups' => 'getSessions']);

    //     return new JsonResponse($jsonSessionList, Response::HTTP_OK, [], true);
    // }

    //effacer un album 
    #[Route('/api/album/{id}', name: 'delete_albums', methods:['DELETE'])]
    public function deleteSession($id, Album $album): JsonResponse
    {
        // $storage = GoogleCloudStorage::getInstance(AlbumController::BUCKET_NAME);
        $pictureList = $album->getPictures();

        

        foreach($pictureList as $picture){
            $pictureFileName = $picture->getFileName();
            $pictureThumbnail = $picture->getThumbnail();
            // $storage->setObjectName($pictureFileName);
            // $storage->getObject()->delete();
            // $storage->setObjectName($pictureThumbnail);
            // $storage->getObject()->delete();
            $this->cs->setObjectName($pictureFileName);
            $this->cs->getObject()->delete();
            $this->cs->setObjectName($pictureThumbnail);
            $this->cs->getObject()->delete();

        }
        
        $this->em->remove($album);
        $this->em->flush();
        
        
        return new JsonResponse('Suppression ok !', Response::HTTP_OK, [], true);
    }

    // get one album
    #[Route('/api/album/{id}', name: 'get_one_album', methods:['GET'])]
    public function getAbumById(Request $request ): JsonResponse
    {
    // récuépration du query param page - default 1
    $page = $request->query->getInt('page', 1);
    // récupération du query param limit défaut 10
    $limit = $request->query->getInt('limit', 10);
    $id = $request->get('id');

    // offset = reprise : page - 1 * la limite : 
    // page 1 : offset 0,
    // page2 : offset 10 -> on charge à partir de 11 etc etc 
    $offset = ($page - 1) * $limit;

    // requête ne bdd 
    $pictures = $this->em
        ->getRepository(Picture::class)
        ->findPictureByAlbum($id, $limit, $offset);
        // ->findBy([], ['id' => 'ASC'], $limit, $offset);
    
    $picturesArray = [];

    // génération de l'url signed de 15 minutes 
    foreach ($pictures as $picture) {
        $pictureFileName = $picture->getThumbnail();
        $this->cs->setObjectName($pictureFileName);
        $url = $this->cs->getObject()
                ->signedUrl(new DateTime('+30 minutes'), [
                    'version' => 'v4',
                    'private' => true,
                ]);
                
        $picturesArray[] = [
            'id' => $picture->getId(),
            'name' => $picture->getName(),
            'url' => $url,
            'isActive' => $picture->isIsActive()
        ];
    }

    return new JsonResponse($picturesArray, Response::HTTP_OK);
}

// update One Album
#[Route("/api/album/{id}", name: "album_uppp", methods:['PUT'])]
public function updateAlbum($id, Album $album, Request $request ) : Response
{
   
    $content = $request->toArray();

    // $published = $content['published'];

 
    // !$published ? $album->setIsActive(false) : $album->setIsActive(true); 
   
    if(isset($content['published'])){
        $published = $content['published'];

        !$published ? $album->setIsActive(false) : $album->setIsActive(true); 
    }

    if(isset($content['album_name'])){
        $name = $content['album_name'];
        $album->setName($name);
        $this->em->persist($album);
    };
    
    if(isset($content['album_moment']) && $moment = $content['album_moment']){
        $matin = $moment == "matin" ? true : false;
        $album->setMorning($matin);
        $this->em->persist($album);

    };

    if(isset($content['album_category']) && $categoryId = $content['album_category']){
        $category = $this->em->getRepository(Category::class)->find($categoryId);
        $album->setCategory($category);
        $this->em->persist($album);
    };

    if(isset($content['album_type']) && $typeAlbumId = $content['album_type']){
        $type = $this->em->getRepository(Products::class)->find($typeAlbumId);
        $album->setProduct($type);
        $this->em->persist($album);
    };



    
    
    $this->em->flush();
  

    return new Response('updated');
}

#[Route('/api/album/{albumId?}', name: 'album_post', methods: ['POST'])]
// #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants')]
    public function upload(Request $request, $albumId)
    {
       
        // récupère les fichiers à uploader
        $files = $request->files->get('files');
        // récupère le nom de la session 
        $OriginalfileName = $files[0]->getClientOriginalName();
        

        $albumName = $request->get('album_name');
        // récupère l'entité session correspondante 
        $album = $this->em->getRepository(Album::class)->findOneByName($albumName);

        if(isset($albumId)){
            $album = $this->em->getRepository(Album::class)->find($albumId);
            $product = $album->getProduct();
        }
        

        // Détermine si c'est une session du matin ou non 
        $moment = $request->get('album_moment');

        // Récupère le type de l'abum 
        $type = $request->get('album_type');

        // récupération de la catégorie de l'album 
        $categoryId = $request->get('album_category');
        $category = $this->em->getRepository(Category::class)->find($categoryId);

        $matin = $moment == "matin" ? true : false;

        $date = $request->get('album_date');

        $dateImmutable = DateTimeImmutable::createFromFormat('d/m/Y', $date);

        if(!$album){
            $album = new Album();
            $album->setCreatedAt(new DateTimeImmutable);
            
        } 
        if($albumName){
            $album->setName($albumName);
        }
        if($moment){
            $album->setMorning($matin);
        }
        
        if($category){
            $album->setCategory($category);
        }
        
        if($type){
            $product = $this->em->getRepository(Products::class)->find($type);
            $album->setProduct($product);
        }
        

        // Initialisation du tableau d'extensions
        $extensions = ['jpg', 'png'];

        // initialisation du nom de bucket 
        // $bucketName = AlbumController::BUCKET_NAME;

        // Récupération d'une instance de GoogleCloudStorage ( singleton )
        // $storage = GoogleCloudStorage::getInstance($bucketName);

        // récupération du bucket
        $bucket = $this->cs->getClient();

        if (empty($files)) {
            throw new \Exception('No file was uploaded.');
        }

     
        // boucle sur les fichiers et stocke chaque fichier 
        foreach ($files as $file) {
            
            if(!in_array($file->guessExtension(), $extensions) || $file->getSize() > self::MAX_FILE_SIZE ){
                $failureUpload[] = $file;

                return new JsonResponse('echec, un fichier n\'est pas valide en taille ou en extension');
            } 

                // crée un nom de fichier unique
                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                // récupération du nom original 
                $originalName = strtolower(explode('.',$file->getClientOriginalName())[0]);
               
                // instanciaiton d'un nouvel objet picture et setting
                $picture = new Picture();

                $picture->setName($originalName);
                $picture->setFileName($filename);
                $picture->setAlbum($album);

                $thumbnailName = 'thumbnail_' . $filename;
                $picture->setThumbnail($thumbnailName);
                $picture->setProduct($product);


                // resize de l'image via ImageOptimizer service
                $this->imgOptimizer->resize($file, $originalName);

                // récupération des streams des images dans le dossier
                $fileContent = file_get_contents("../public/images/" . $originalName . ".jpg");
                
                // upload des thmbnails vers le googleClood
                $object = $bucket->upload($fileContent,
                [
                    "predefinedAcl" => 'private',
                    'name' => 'thumbnail_' . $filename
                ]);

                // Suppression des fichiers du dossier image
                $path = "../public/images";

                $finder = new Finder();
                $finder->in($path);

                $fileSystem = new Filesystem();
                $fileSystem->remove($finder);

                // upload en mode privé des fichiers HD
                $object = $bucket->upload(fopen($file, 'r'), [
                    'predefinedAcl' => 'private',
                    'name' => $filename
                ]);

                // inscription en bdd
                $this->em->persist($picture);
                $this->em->flush();
                
        }
        
        //Mise à jour de la photo de couverture de l'album
        $coverPicture = $this->em->getRepository(Picture::class)->findFirstPictureByAlbum($album->getId());
        
        $album->setCoverPicture($coverPicture->getThumbnail());

        // Inscription en BDD
        $this->em->persist($album);
        $this->em->flush(); 

        
       
        // renvoie une réponse de succès avec les noms de fichiers et les URL signées pour accéder aux fichiers
        // $urls = [];
        // foreach ($filenames as $filename) {
        //     $url = $this->generateUrl('file_download', ['filename' => $filename], true);
        //     $urls[] = $url;
        // }
        return new JsonResponse(['photos uploadées avec succès']);
    }

        // get one album info 
        #[Route('/api/album/{id}/info', name: 'get_one_album_info', methods:['GET'])]
        public function getAbumInfoById($id, Album $album, Request $request ): JsonResponse
        {
        
            $jsonAlbum = $this->serializer->serialize($album, 'json', ['groups' => 'getAlbumInfo']);

            return new JsonResponse($jsonAlbum, Response::HTTP_OK, [], true);

    }
}



;