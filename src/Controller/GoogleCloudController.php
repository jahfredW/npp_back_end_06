<?php


namespace App\Controller;


use DateTime;
use DateTimeImmutable;
use App\Entity\Picture;
use App\Entity\Album;
use Google\Cloud\Storage\StorageClient;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Storage\GoogleCloudStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\Optimizer\ImageOptimizer;


class GoogleCloudController extends AbstractController
{
    private $em;
    private $imgOptimizer;
    const BUCKET_NAME = 'npp_photos_prod';
    const MAX_FILE_SIZE = 15000000;

    public function __construct(EntityManagerInterface $em, ImageOptimizer $imgOptimizer){
        $this->em = $em;
        $this->imgOptimizer = $imgOptimizer;
    }
    #[Route('/api/cloudgoogle', name: 'cloud', methods: ['POST'])]
    public function upload(Request $request)
    {
       
        // récupère les fichiers à uploader
        $files = $request->files->get('files');
        // récupère le nom de la session 
        $albumName = $request->get('album_name');
        // récupère l'entité session correspondante 
        $album = $this->em->getRepository(Album::class)->findOneByName($albumName);

        // Détermine si c'est une session du matin ou non 
        $moment = $request->get('album_moment');

        $category = $request->get('album_category');

        $matin = $moment == "matin" ? true : false;

        $date = $request->get('session_date');
        $dateImmutable = DateTimeImmutable::createFromFormat('d/m/Y', $date);

        if(!$album){
            $album = new Album();
            $album->setName($albumName);
            $album->setMorning($matin);
            $album->setCategory($category);
        }

        // Initialisation du tableau d'extensions
        $extensions = ['jpg', 'png'];

        // initialisation du nom de bucket 
        $bucketName = GoogleCloudController::BUCKET_NAME;

        // Récupération d'une instance de GoogleCloudStorage ( singleton )
        $storage = GoogleCloudStorage::getInstance($bucketName);

        // récupération du bucket
        $bucket = $storage->getClient();

        if (empty($files)) {
            throw new \Exception('No file was uploaded.');
        }

     
        // boucle sur les fichiers et stocke chaque fichier dans Gaufrette
        foreach ($files as $file) {
            
            if(!in_array($file->guessExtension(), $extensions) || $file->getSize() > GoogleCloudController::MAX_FILE_SIZE ){
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
             
                $picture->setCreatedAt($dateImmutable);
                $picture->setAlbum($album);

                // upload en mode privé 
                $object = $bucket->upload(fopen($file, 'r'), [
                    'predefinedAcl' => 'private',
                    'name' => $filename
                ]);
                


                // inscription en bdd
                $this->em->persist($picture);
                $this->em->flush();
                
        }
        
        $coverPicture = $this->em->getRepository(Picture::class)->findFirstPictureByAlbum($album->getId());
        
        $album->setCoverPicture($coverPicture->getFileName());

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
}
