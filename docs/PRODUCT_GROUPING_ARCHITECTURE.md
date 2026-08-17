# Product Grouping / Variant Architecture

Reusable OpenCart guidance extracted from the HYPER Product Models audit and the SiteZilla Product Groups redesign.

## Core rule

Treat **product grouping** and **catalog deduplication** as different responsibilities.

A grouping domain should answer:

```text
Which real product_ids belong to one family?
Which product is the main/default item?
Which profile/dimensions describe the variants?
In which order should variants be shown?
```

It should not globally redefine what `product_id` means to OpenCart.

## Avoid global SQL identity rewriting

Do not make the base group engine patch unrelated catalog queries to replace:

- `product_id`;
- `COUNT(DISTINCT product_id)`;
- `GROUP BY product_id`;
- sorting aliases;
- theme/filter SQL internals.

That approach couples the domain model to every category, search, filter, special, review, theme and extension query that happens to expose products.

## Preferred architecture

```text
group tables / membership
        ↓
group service / batch lookup
        ↓
real sellable product_ids
        ↓
product-page variant selector

optional catalog collapse
        ↓
explicit adapter per integration
(Journal3 / OCFilter / search / category)
```

Every sellable variant should remain a real OpenCart product unless the target project explicitly models variants differently.

## Legacy migration rule

When replacing an existing grouping module:

1. read old group/membership tables as migration sources;
2. preserve the old data during the first migration;
3. make import idempotent;
4. record the legacy identity on the new entity when useful;
5. verify the new groups before disabling the old runtime;
6. delete legacy data only through a separate explicit purge step.

Do not couple first import with destructive cleanup.

## Performance rule

Prefer batch group/member resolution over per-card N+1 queries.

For a category page, resolve memberships and required variant data for the visible product set in batches, then let the renderer consume that prepared map.

## Compatibility rule

Keep theme/filter adapters removable and isolated from the grouping core. A Journal3-specific collapse query should not become the persistence or domain model for product groups.
