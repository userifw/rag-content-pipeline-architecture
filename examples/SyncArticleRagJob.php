<?php

declare(strict_types=1);

namespace Architecture\Examples;

final class SyncArticleRagJob
{
    public function __construct(private readonly int $articleId) {}

    public function uniqueId(): string
    {
        return (string) $this->articleId;
    }

    public function handle(ArticleRepository $articles, RagIndex $rag): void
    {
        $article = $articles->findForRag($this->articleId);

        if ($article === null) {
            return;
        }

        if (! $rag->isEligible($article)) {
            $rag->remove($article->id);
            $articles->markRagSynchronized($article->id);
            return;
        }

        $rag->reconcile($article);
        $articles->markRagSynchronized($article->id);
    }
}
