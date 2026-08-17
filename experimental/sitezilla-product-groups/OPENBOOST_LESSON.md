# Reusable OpenCart lesson from Product Groups work

When replacing a third-party grouping/variant module, separate **domain grouping** from **catalog deduplication**.

Do not make the group engine globally rewrite `product_id`, `COUNT(DISTINCT ...)`, `GROUP BY`, sorting or third-party filter SQL just to make a family appear as one catalog item. Prefer:

1. stable module-owned group and membership tables;
2. real product IDs for every sellable variant;
3. a batch lookup/service for group presentation;
4. an explicit, optional catalog-collapse adapter for each search/filter/theme integration;
5. compatibility adapters isolated from core domain logic;
6. idempotent legacy import that never deletes source data during first migration.

This pattern reduces conflicts with Journal3, OCFilter and custom category queries and gives a safe migration path from legacy HPM-style modules.
