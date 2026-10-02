# Failure Model

The architecture assumes every external dependency can fail, time out or return an unusable result.

## Embeddings

Provider errors are not converted into empty vectors. The queue job fails and can retry. Missing semantic state must never be interpreted as "no duplicate found."

## Vector database

Relational application state remains authoritative. Qdrant failure blocks semantic processing but does not create a false successful workflow state.

## Duplicate races

Initial duplicate detection is insufficient because another worker may accept a similar article before publication. The final publication boundary repeats the check under a lock.

## Stale vectors

Deletion is part of synchronization. When eligibility or target scope changes, obsolete vector points are removed.

## Model quality

AI-generated text is a draft. Deterministic checks can reject known bad patterns, while semantic quality review can route questionable output to moderation.

## External publication

A failed remote publication must not be represented as successful local publication. Retry logic should use persisted publication identity and state rather than blind repeated sends.
