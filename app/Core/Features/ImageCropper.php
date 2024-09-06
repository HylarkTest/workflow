<?php

declare(strict_types=1);

namespace App\Core\Features;

use Intervention\Image\Size;
use Intervention\Image\Point;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Imagick\Color;

class ImageCropper
{
    /**
     * @param array{
     *          url?: string,
     *          xOffset?: int,
     *          yOffset?: int,
     *          width?: int,
     *          height?: int,
     *          rotate?: int,
     * } $cropInfo
     */
    public function __construct(protected UploadedFile $uploadedFile, protected array $cropInfo) {}

    public function isCroppable(): bool
    {
        if (is_array($dimensions = $this->uploadedFile->dimensions()) && isset($this->cropInfo['width'], $this->cropInfo['height'])) {
            $width = $dimensions[0];
            $height = $dimensions[1];

            return $this->cropInfo['width'] !== $width || $this->cropInfo['height'] !== $height;
        }

        return false;
    }

    public function cropAndSave(): UploadedFile
    {
        $savedPath = $this->cropImage();

        return new UploadedFile(
            $savedPath,
            $this->uploadedFile->getClientOriginalName(),
            $this->uploadedFile->getClientMimeType(),
            $this->uploadedFile->getError(),
        );
    }

    /**
     * @throws \ImagickException
     */
    protected function cropImage(): string
    {
        $rotate = $this->cropInfo['rotate'] ?? false;
        // Need to rename it with the file extension otherwise Intervention/Image can't encode properly
        $savePath = '/tmp/'.uniqid().'.'.$this->uploadedFile->getClientOriginalExtension();
        if ($this->uploadedFile->getMimeType() === 'image/gif') {
            $imagick = new \Imagick($this->uploadedFile->getRealPath());
            $imagick = $imagick->coalesceImages();
            $color = (new Color('#ffffff'))->getPixel();
            do {
                if ($rotate !== false) {
                    $imagick->rotateImage($color, $rotate);
                }
                if (isset($this->cropInfo['width'], $this->cropInfo['height'], $this->cropInfo['xOffset'], $this->cropInfo['yOffset'])) {

                    $cropped = new Size($this->cropInfo['width'], $this->cropInfo['height']);
                    $position = new Point($this->cropInfo['xOffset'], $this->cropInfo['yOffset']);

                    $imagick->cropImage($cropped->width, $cropped->height, $position->x, $position->y);
                }
                $imagick->setImagePage(0, 0, 0, 0);
            } while ($imagick->nextImage());

            $imagick->deconstructImages();
            $imagick->writeImages($savePath, true);
        } else {
            $image = (new ImageManager(['driver' => 'imagick']))->make($this->uploadedFile);
            if ($rotate !== false) {
                $image->rotate($rotate * -1);
            }
            if (isset($this->cropInfo['width'], $this->cropInfo['height'])) {
                $image->crop($this->cropInfo['width'], $this->cropInfo['height'], $this->cropInfo['xOffset'] ?? 0, $this->cropInfo['yOffset'] ?? 0);
            }
            $image->save($savePath);
        }

        return $savePath;
    }
}
