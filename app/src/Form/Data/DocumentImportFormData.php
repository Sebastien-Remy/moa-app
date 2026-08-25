<?php

namespace App\Form\Data;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocumentImportFormData
{
    /**

     * @var list<UploadedFile>

     */

    public array $uploadedFiles = [];

    public ?\DateTimeImmutable $receivedAt = null;
}
