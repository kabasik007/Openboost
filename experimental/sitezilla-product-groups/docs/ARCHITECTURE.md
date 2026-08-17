# Architecture — SiteZilla Product Groups

## Domain model

```text
Profile
  └─ dimensions[]
       ├─ source: field | attribute | filter
       ├─ source_id / source_key
       ├─ label
       └─ renderer: text | image | swatch

Group
  ├─ main_product_id
  ├─ profile_id
  ├─ store relations
  └─ items[]
       ├─ product_id
       ├─ sort_order
       └─ image_override
```

A group does not replace a product. The selected variant is always a real OpenCart product.

## Data ownership
- `sz_product_group`: group identity and main product.
- `sz_product_group_item`: group membership/order.
- `sz_product_group_profile`: reusable builder profile.
- `sz_product_group_to_store`: multistore visibility.
- legacy HPM tables are read-only migration sources.

## Runtime flow

```text
product page
  -> catalog controller/model
  -> current product membership lookup
  -> one group query + one batch product query
  -> dimension resolver
  -> module template
```

No global `product_id`, `COUNT()` or `GROUP BY` rewrite is allowed in the domain engine.

## Compatibility adapters
Theme/filter compatibility is isolated from core behavior. Journal3 may receive a dedicated adapter OCMOD later, but the base module must remain functional with the default product page.

## Legacy import
The importer may read `hpmodel_links(parent_id, product_id, sort, image, type_id)` and create one new group per legacy parent. It never deletes HPM data. Re-running import must be safe and skip already imported legacy parents.
