# Page block system

Pages store ordered JSON blocks in `pages.blocks`. Filament edits the schema registered in `app/Filament/Blocks/BlockRegistry.php`; translations use `en`, `my`, and `th` keys.

## Current rendering path

1. `FrontendController` serves the Vue shell for published page URLs.
2. `resources/frontend/composables/useBlocks.js` requests `/api/frontend/items/pages` filtered by permalink.
3. `FrontendContentController` converts saved block types (`vue_*` → `block_*`, legacy types → `native_*`) and localized nested items to the frontend contract. Blank translation fields fall back to English.
4. `resources/frontend/components/BlockRenderer.vue` selects a Vue component in `components/blocks`. `NativeBlock.vue` supports the six legacy types and rich text.

Service pages use `/services/{slug}`, including `/services/storage`. `/storage/*` is reserved for uploaded files. The old Blade block templates are not the public Vue rendering path.

## Adding a block

- Add its type, schema, label and appropriate create-option group in `BlockRegistry`.
- Define shared fields and translated fields in its Filament schema.
- Add nested item conversion in `FrontendContentController::block()` when needed. Components expect translation arrays with `languages_code`, not the saved locale-keyed object.
- Add its Vue component and mapping in `BlockRenderer`. Keep interactions component-scoped, clean up plugins on unmount, and support missing optional images.
- Test an actual saved block and browser fixtures in English, Burmese and Thai at desktop/mobile widths. Verify links, repeaters, blank translations and repeated component instances.
- Run `npm run build` before Laravel rendering tests.

## October 2026 audit

All 25 registered types have schemas and renderers. All 12 published pages were checked at 1440px and 390px. Browser fixtures exercised all 25 types in three languages. No existing page/block records were deleted.

Removed unused masthead review-image input/code, the nonfunctional Transport ID tracking template, an empty FAQ section, a missing process-map decoration, and an invented fallback phone number. The quote banner links to the real Request Quote page. Brand logos use a responsive grid without carousel timing dependencies. Existing contact forms remain connected to the website-submission endpoint.

Regression tests: `PageBlockAuditTest`, `VueWestBlockRegistryTest`, `FrontendIntegrationTest`, `PageCreationWizardTest`, `PageImagePreviewTest`.
