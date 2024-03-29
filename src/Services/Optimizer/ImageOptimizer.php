<?php 

namespace App\Services\Optimizer;

use Imagine\Image\Box;
use Imagine\Gd\Imagine;
use Imagine\Image\Point;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageOptimizer
{
    private const MAX_WIDTH = 2400;
    private const MAX_HEIGHT = 1600;
    private $kernel;

    private $imagine;

    public function __construct(KernelInterface $kernel)
    {
        $this->imagine = new Imagine();
        $this->kernel = $kernel;
    }

    public function resize(string $filename, $originalName)
    {
        $publicDirectory = $this->kernel->getProjectDir() . '/public';
       
        // Récupération des dimensions de l'image d'origine
        list($iwidth, $iheight) = getimagesize($filename);
        $ratio = $iwidth / $iheight;

        // Calcul des dimensions de l'image redimensionnée
        $width = self::MAX_WIDTH;
        $height = self::MAX_HEIGHT;
        if ($width / $height > $ratio) {
            $width = $height * $ratio;
        } else {
            $height = $width / $ratio;
        }

        // Ouverture de l'image d'origine
        $photo = $this->imagine->open($filename);

        // Redimensionnement de l'image
        $photo->resize(new Box($width, $height));

        // Ouverture de l'image du filigrane
        // $watermark = $this->imagine->open($publicDirectory . '/filigrane/back.png');
        $watermark = $this->imagine->open($publicDirectory . '/filigrane/back.png');

        // Calcul des dimensions de l'image du filigrane
        $wWidth = $width * 0.2; // mettre la largeur du watermark à la moitié de la largeur de l'image
        $wHeight = $watermark->getSize()->getHeight() * ($wWidth / $watermark->getSize()->getWidth());

        // Redimensionnement de l'image du filigrane
        $watermark->resize(new Box($wWidth, $wHeight));

        // Positionnement du filigrane
        $position = new Point(($width - $wWidth) / 2, ($height - $wHeight) / 2);

        // Ajout du filigrane à l'image redimensionnée
        $photo->paste($watermark, $position, 40);

        // Sauvegarde de l'image redimensionnée avec filigrane
        $photo->save($publicDirectory . '/images/' .$originalName. ".jpg");

        // list($iwidth, $iheight) = getimagesize($filename);
        // $ratio = $iwidth / $iheight;
        
        // // processus de redimentionnement de l'image
        // $width = self::MAX_WIDTH;
        // $height = self::MAX_HEIGHT;
        // if ($width / $height > $ratio) {
        //     $width = $height * $ratio;
        // } else {
        //     $height = $width / $ratio;
        // }
        
        // $photo = $this->imagine->open($filename);

        // // Appliquer le filigrane sur l'image

        // $watermark = $this->imagine->open("../public/filigrane/back.png");
        // $watermarkSize = $watermark->getSize();
        // $photoSize = $photo->getSize();
        // $position = new Point(
        //     $photoSize->getWidth() - $watermarkSize->getWidth() - 10,
        //     $photoSize->getHeight() - $watermarkSize->getHeight() - 10
        // );
        // $opacity = 50;
        // $photo->paste($watermark, $position, $opacity);
        
        // // $photo->resize(new Box($width, $height))->save($filename);
        // $photo->resize(new Box($width, $height))->save("../public/images/" .$originalName. ".jpg");
      
    }
}