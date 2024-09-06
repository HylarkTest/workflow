<?php

declare(strict_types=1);

namespace App\Core\Support;

/**
 * Class SupportRepository
 *
 * @phpstan-type SupportArticle array{
 *     id: int,
 *     title: string,
 *     description?: string,
 *     description_text?: string,
 *     topics: string[],
 *     hits: int,
 *     thumbsUp: int,
 *     thumbsDown: int,
 *     createdAt: string,
 * }
 * @phpstan-type SupportFolder array{
 *     id?: int,
 *     name: string,
 *     description?: string,
 *     articles?: array<int, SupportArticle>,
 *     articles_count?: int
 * }
 * @phpstan-type SupportCategory array{
 *     id: int,
 *     name: string,
 *     description?: string,
 *     folders?: array<int, SupportFolder>
 * }
 */
interface SupportRepositoryInterface
{
    /**
     * @return SupportCategory[]
     */
    public function getSupportCategories(): array;

    public function getPopularCategories(): array;

    /**
     * @return SupportArticle[]
     */
    public function getSupportArticles(): array;

    /**
     * @return SupportArticle[]
     */
    public function getMostRecentArticles(): array;

    /**
     * @return SupportArticle[]
     */
    public function getRecommendedArticles(): array;

    /**
     * @return SupportArticle[]
     */
    public function searchArticles(string $query): array;

    /**
     * @return string[]
     */
    public function getTopics(): array;

    /**
     * @return string[]
     */
    public function getPopularTopics(): array;

    /**
     * @return SupportArticle
     */
    public function getArticle(string $id): array;

    /**
     * @return SupportFolder
     */
    public function getFolder(int $folderId): array;
}
