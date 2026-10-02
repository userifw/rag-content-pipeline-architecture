<?php

declare(strict_types=1);

namespace Architecture\Examples;

final class PublicationBoundary
{
    public function __construct(
        private readonly LockManager $locks,
        private readonly DuplicateDetector $duplicates,
        private readonly QualityGate $quality,
        private readonly RagIndex $rag,
    ) {}

    public function evaluate(PublicationDraft $draft): PublicationDecision
    {
        if ($draft->sourceLength < 500) {
            return PublicationDecision::reject('source_too_short');
        }

        if ($draft->looksPromotional()) {
            return PublicationDecision::moderate('promotional_pattern');
        }

        return $this->locks->block(
            'publication:'.$draft->scopeId,
            function () use ($draft): PublicationDecision {
                if ($this->duplicates->exists($draft)) {
                    return PublicationDecision::moderate('semantic_duplicate');
                }

                if (! $this->quality->review($draft)->allowed) {
                    return PublicationDecision::moderate('quality_gate');
                }

                $this->rag->makeVisible($draft);

                return PublicationDecision::allow();
            }
        );
    }
}
