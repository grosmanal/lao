<?php

namespace App\Service;

use App\Entity\Doctor;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;

class InitialAvatarGenerator
{
    private $imageDirectory;

    public function __construct($vichUploaderMapping, $vichUploaderAvatarParameters)
    {
        $this->imageDirectory = $vichUploaderAvatarParameters[$vichUploaderMapping]['upload_destination'];
    }

    public function generate(Doctor $doctor): string
    {
        // Nommage du fichier de la même façon que Vich\UploaderBundle\Naming\UniqidNamer
        $imageName = \uniqid('', true) . '.png';

        $avatar = new InitialAvatar();

        $image = $avatar
            ->name($doctor->getDisplayName())
            ->generate()
            ->save(sprintf("%s/%s", $this->imageDirectory, $imageName))
        ;

        return $imageName;
    }
}
