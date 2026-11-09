<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FileTypeRule implements Rule
{
    protected $allowedMimeTypes;
    protected $uploadedType;
    protected $attribute; // <-- store attribute name

    public function __construct(array $allowedMimeTypes)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
    }

    public function passes($attribute, $value)
    {
        $this->attribute = $attribute; // <-- store it for message()
        $this->uploadedType = $value->getMimeType();
        return in_array($this->uploadedType, $this->allowedMimeTypes);
    }

    public function message()
    {
        $fileLabel = $this->extractLabel($this->attribute);
        return "Invalid file type in {$fileLabel}";
    }

    protected function extractLabel($attribute)
    {
        // For example: "attachments.0" -> "attachments[0]"
        if (preg_match('/\.(\d+)$/', $attribute, $matches)) {
            return "file #{$matches[1]}";
        }

        return $attribute;
    }
}
