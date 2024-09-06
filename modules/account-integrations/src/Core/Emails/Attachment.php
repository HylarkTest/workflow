<?php

declare(strict_types=1);

namespace AccountIntegrations\Core\Emails;

use GuzzleHttp\Psr7\Stream;

/**
 * @phpstan-type AttachmentInfo = array{
 *     file: \Illuminate\Http\UploadedFile,
 *     isInline: boolean,
 *     contentId?: string|null,
 *     name?: string,
 * }
 */
class Attachment
{
    public ?string $id;

    public ?string $contentId;

    public string $name;

    public string|Stream $content;

    public string $fileType;

    public string $link;

    public bool $isInline;

    public function __construct(
        array $attachmentArray,
    ) {
        $this->id = $attachmentArray['id'];
        $this->contentId = $attachmentArray['contentId'];
        $this->name = $attachmentArray['name'];
        $this->content = $attachmentArray['content'];
        $this->fileType = $attachmentArray['fileType'];
        $this->link = $attachmentArray['link'];
        $this->isInline = $attachmentArray['isInline'];
    }
}
