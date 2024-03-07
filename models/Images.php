<?php

namespace app\models;

use Imagine\Image\Box;
use Imagine\Image\Point;
use Yii;
use yii\helpers\Inflector;
use yii\imagine\Image;

/**
 * This is the model class for table "images".
 *
 * @property int $id
 * @property string $filename
 * @property string $uploaded_at
 */
class Images extends \yii\db\ActiveRecord
{
    public $files;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['files'], 'file', 'extensions' => 'png, jpg, gif', 'mimeTypes' => 'image/png, image/jpeg, image/gif', 'maxFiles' => 5],
        ];
    }

    public function upload(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if ($transaction = Yii::$app->db->beginTransaction()) {
            try {
                foreach ($this->files as $file) {
                    $baseName = $this->transliterateFileName($file->baseName);
                    $newFileName = $this->generateUniqueFileName($baseName, $file->extension);

                    $file->saveAs('uploads/' . $newFileName);
                    $image = new self();
                    $image->filename = $newFileName;
                    $image->save();
                }

                $transaction->commit();
                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
                return false;
            }
        }
        return false;
    }

    public function generatePreviewDataUri(int $previewWidth = 50, int $previewHeight = 50): string
    {
        $imagePath = "uploads/{$this->filename}";
        $image = Image::getImagine()->open($imagePath);
        $size = $image->getSize();

        $aspectRatio = $size->getWidth() / $size->getHeight();

        $newWidth = $previewWidth;
        $newHeight = (int)($previewWidth / $aspectRatio);

        if ($newHeight > $previewHeight) {
            $newHeight = $previewHeight;
            $newWidth = (int)($previewHeight * $aspectRatio);
        }

        $resizedImage = $image->thumbnail(new Box($newWidth, $newHeight));
        $newImage = Image::getImagine()->create(new Box($previewWidth, $previewHeight));

        $x = (int)(($previewWidth - $newWidth) / 2);
        $y = (int)(($previewHeight - $newHeight) / 2);

        $newImage->paste($resizedImage, new Point($x, $y));
        $format = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        return 'data:image/' . $format . ';base64,' . base64_encode($newImage->get($format));
    }

    private function transliterateFileName(string $fileName): string
    {
        return strtolower(Inflector::transliterate($fileName));
    }

    private function generateUniqueFileName(string $baseName, string $extension): string
    {
        $newFileName = $baseName . '.' . $extension;

        while (file_exists("uploads/{$newFileName}")) {
            $newFileName = $baseName . '_' . time() . '_' . Yii::$app->security->generateRandomString(5) . '.' . $extension;
        }

        return $newFileName;
    }
}
