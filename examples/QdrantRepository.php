<?php

declare(strict_types=1);

namespace Architecture\Examples;

final class QdrantRepository
{
    public function __construct(private readonly VectorTransport $transport) {}

    public function reconcile(int $articleId, array $expectedPoints): void
    {
        $existingIds = $this->transport->pointIdsForArticle($articleId);
        $expectedIds = array_keys($expectedPoints);

        foreach ($expectedPoints as $pointId => $point) {
            $this->transport->upsert(
                $pointId,
                $point['vector'],
                $point['payload'],
            );
        }

        $staleIds = array_values(array_diff($existingIds, $expectedIds));

        if ($staleIds !== []) {
            $this->transport->delete($staleIds);
        }
    }
}
