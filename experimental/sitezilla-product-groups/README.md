# SiteZilla Product Groups

Experimental OpenCart 2.3 product-group/variant engine derived from the business concepts of HYPER Product Models, not from its obfuscated implementation.

## Goals
- keep every variant as a real OpenCart `product_id`;
- group products without rewriting core SQL globally;
- support profile-driven dimensions (`field`, `attribute`, `filter`);
- preserve existing stores through a legacy HPM importer;
- isolate Journal3 and other theme integrations behind adapters;
- keep admin builder, persistence, catalog rendering and compatibility code separate.

## v0.1 scope
- group/profile schema and install lifecycle;
- admin builder for groups and dimensions;
- manual product membership and item ordering;
- legacy `hpmodel_links` import into the new schema;
- catalog API to load the current group and its variants;
- thin product-page OCMOD injection only.

## Compatibility
Initial target: OpenCart 2.3.x. Code intentionally avoids syntax newer than PHP 5.6 because the first target stores are legacy OC2.3 installations. A later compatibility layer can raise the floor per target project.

## Status
Experimental. Keep this branch unmerged until the module is validated on a staging store.
