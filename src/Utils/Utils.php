<?php 

namespace App\Utils;

use DateTimeImmutable;

class Utils
{
    public static function pictureExtension($picture)
    {
        $extensionTab = ['jpg, png'];

        
    }

    public static function cleanUp($data)
    {
        $data = trim($data);
        $data = htmlspecialchars($data);
        $data = stripcslashes($data);
        return $data;
    }

    // public static function dateDecoder($date)
    // {
    //     $decodedDate = urldecode($date);
    //     $sliceDate = substr($decodedDate, 0 , (strpos( $decodedDate, 'G') - 1) );
    //     $dateTime = DateTimeImmutable::createFromFormat('D M d Y H:i:s', $sliceDate);

    //     if($dateTime != null){
    //         $dateTime = $dateTime->setTime(0,0,0);
    //     }
        

    //     return $dateTime;
    // }

    public static function dateDecoder($date)
    {
        if($date != null){
            $decodedDate = urldecode($date);
            $sliceDate = substr($decodedDate, 0 , (strpos( $decodedDate, 'G') - 1) );
            $dateTime = DateTimeImmutable::createFromFormat('D M d Y H:i:s', $sliceDate);
            
            if($dateTime != null){
            $dateTime = $dateTime->setTime(0,0,0);
            
             return $dateTime;
        }
        
        }
        

       
    }
}



