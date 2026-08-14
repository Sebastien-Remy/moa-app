<?php

namespace App\Form\Data;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DocumentImportFormData
{
    public ?UploadedFile $uploadedFile = null;

    public ?\DateTimeImmutable $receivedAt = null;
}
