<?php

declare(strict_types=1);

namespace App\Models\Support;

enum ArticleStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
}
