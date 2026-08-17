---
name: opencart-ui-ux
description: UI/UX standard for OpenCart module frontends and admin screens: mobile-first responsive design, theme presets, custom themes, design tokens, accessibility, and modern admin interaction patterns.
---

# OpenCart Module UI/UX

Use this skill whenever an OpenCart module has visible frontend UI, admin UI, buttons, colors, cards, badges, forms, tabs, filters, tables, modals, drawers, responsive layouts, or configurable appearance.

## Core principle

A module UI is part of the product, not an afterthought.

Default Openboost behavior:

- frontend is **mobile-first**;
- admin screens are responsive and usable on modern laptop/tablet/mobile widths where practical;
- visual styles are module-owned and namespaced;
- buttons/colors are not hard-coded throughout templates;
- modules with meaningful custom visual UI should support theme presets;
- modules with theme presets should normally support a `Custom` theme too;
- accessibility, touch targets, loading states, empty states, errors, and destructive confirmations are part of completion.

Do not redesign a host theme globally just because one module needs UI.

## Inspect before designing

Before changing UI, inspect the target project for:

- active catalog theme and Journal3/custom theme conventions;
- adjacent first-party/custom module screens;
- Bootstrap/version or other UI framework already present;
- admin theme/layout conventions;
- existing icon library;
- existing CSS variables/design tokens;
- responsive breakpoints;
- modal/dropdown/select libraries;
- language/RTL implications where relevant;
- OCMOD/theme overrides touching the same DOM.

Reuse compatible project conventions instead of introducing a second UI framework for one module.

## Mobile-first frontend

Build the narrow-screen layout first, then enhance it for larger widths.

Prefer CSS shaped like:

```css
.sz-module { /* phone/base */ }

@media (min-width: 768px) {
  .sz-module { /* tablet+ enhancement */ }
}

@media (min-width: 1200px) {
  .sz-module { /* desktop enhancement */ }
}
```

The exact breakpoints should follow the target theme when one already exists.

Do not create a desktop layout and then patch it with dozens of `max-width` overrides.

### Mobile requirements

For frontend modules verify:

- no horizontal page overflow;
- primary actions remain reachable;
- controls have practical touch targets (roughly 44px when possible);
- forms use one-column flow on narrow screens unless a denser layout is clearly better;
- tables become scrollable, stacked, condensed, or card-based instead of breaking the viewport;
- modals/drawers fit the viewport and remain dismissible;
- dropdowns/selects do not render off-screen;
- text does not rely on fixed heights;
- images/icons scale correctly;
- sticky elements do not cover important controls;
- hover is never the only way to access functionality.

Test at narrow widths, not only with browser desktop resizing.

## Modern admin UI

OpenCart admin UI does not need to look dated simply because the underlying platform is old.

A custom module admin may use a cleaner modern composition while still respecting OpenCart navigation, permissions, tokens, forms, and existing libraries.

Preferred patterns where useful:

- clear page title + short context/subtitle;
- compact action bar;
- cards/sections instead of one enormous undifferentiated form;
- tabs only when they reduce cognitive load;
- accordions for secondary/advanced settings;
- sticky save/action area for long forms when it does not conflict with the admin theme;
- searchable selects for large category/product/manufacturer lists;
- filters close to the data they affect;
- bulk actions near selection state;
- pagination that remains usable on mobile;
- inline validation near the invalid field;
- explicit loading/saving states;
- empty states that explain the next action;
- confirmation for destructive actions;
- success/error feedback that is visible and specific;
- responsive grids that collapse cleanly.

Do not hide required functionality behind clever visual interactions that are hard to discover.

## Theme system requirement

If a module exposes visible colored UI such as:

- primary/secondary buttons;
- floating buttons/badges;
- cards/panels;
- status labels;
- checkout/action blocks;
- seller widgets;
- notifications;
- frontend forms;
- branded module sections;

then do not scatter literal colors across templates and JavaScript.

Create a small module theme system.

### Recommended modes

At minimum when theming is useful:

```text
Default / Auto
Preset 1
Preset 2
...
Custom
```

`Default / Auto` should integrate with the host theme as much as practical.

Presets should be meaningful reusable looks rather than ten nearly identical colors.

Example conceptual presets:

```text
Default
Blue
Green
Chocolate
Dark
Light
Custom
```

The actual preset names/colors belong to the product being built; Openboost does not require these exact names.

## Design tokens

Represent appearance as a small set of semantic tokens, not raw per-element colors.

Typical tokens:

```text
primary
primary_hover
primary_text
secondary
accent
success
warning
danger
surface
surface_alt
border
text
text_muted
radius
shadow
```

Not every module needs every token.

Prefer semantic names such as `danger` over names such as `red_button`.

### CSS variables

When browser/project compatibility permits, expose module tokens through one namespaced wrapper:

```html
<div class="sz-module" style="
  --sz-primary: #0089cc;
  --sz-primary-text: #ffffff;
  --sz-surface: #ffffff;
  --sz-text: #1f2937;
  --sz-radius: 12px;
">
```

and consume them in module CSS:

```css
.sz-module .sz-btn-primary {
  background: var(--sz-primary);
  color: var(--sz-primary-text);
  border-radius: var(--sz-radius);
}
```

Prefer generating one wrapper/token block over writing dynamic inline styles on every element.

If the target browser policy cannot use CSS variables, keep the same token model and compile/render a namespaced stylesheet from the settings instead.

## Custom theme editor

When `Custom` is supported, the admin should expose only useful design controls.

Good controls may include:

- primary color;
- accent color;
- text/on-primary color when automatic contrast cannot safely choose it;
- background/surface color;
- border radius;
- optional button style/size;
- optional dark/light mode behavior.

Prefer a color picker plus editable HEX value when compatible with the existing admin stack.

Do not expose 40 low-level CSS fields to ordinary users unless the module is explicitly a visual builder.

### Custom CSS

Raw custom CSS is optional and should be an advanced escape hatch, not the primary theme system.

If provided:

- keep it module-scoped where possible;
- label it Advanced;
- do not require it for normal theming;
- do not mix untrusted arbitrary CSS into contexts where security/product requirements prohibit it.

## Settings model

Theme configuration should use stable module-prefixed keys.

Conceptually:

```text
module_x_theme = default|blue|green|dark|custom
module_x_theme_custom[primary]
module_x_theme_custom[accent]
module_x_theme_custom[surface]
module_x_theme_custom[text]
module_x_theme_custom[radius]
```

Use the project's existing settings conventions rather than inventing a new storage engine only for colors.

For multistore projects, decide explicitly whether theme settings are global or `store_id` scoped.

## Do not couple behavior to color

JavaScript must not decide behavior by checking CSS colors/classes such as:

```js
if (button.css('background-color') === 'rgb(...)') { ... }
```

Behavior uses semantic state/data attributes/classes. Theme changes appearance only.

Good separation:

```text
state: is-active / is-error / data-status
appearance: CSS token maps that state to a color
```

## Component states

Every reusable interactive component should consider the states it actually needs:

- default;
- hover (pointer devices);
- focus-visible;
- active/pressed;
- disabled;
- loading;
- success;
- warning;
- error.

A theme preset is incomplete if normal state looks good but focus/error/disabled states become unreadable.

## Accessibility and contrast

Theme customization must not make essential controls unreadable.

At minimum:

- maintain clear text/background contrast;
- preserve visible keyboard focus;
- do not communicate status with color alone when a label/icon can clarify it;
- buttons need meaningful text/accessible labels;
- icon-only admin actions need title/ARIA labeling where supported;
- form inputs need associated labels or equivalent accessible names;
- error messages should identify the affected field.

If automatic custom colors can create unsafe contrast, calculate/choose an appropriate foreground or warn the admin.

## Icons

Reuse the existing OpenCart/admin/theme icon library when practical.

Do not mix Font Awesome, Bootstrap Icons, Material Icons, SVG sprite packages, and custom icon fonts in one module without a concrete reason.

For custom SVG icons:

- namespace them;
- keep sizing consistent;
- prefer `currentColor` where theme coloring is desired.

## JavaScript UI behavior

UI JavaScript should be progressive and module-scoped.

Rules:

- namespace selectors/events where practical;
- avoid global element IDs that can collide on repeated module instances;
- do not depend on DOM from an unrelated theme unless the integration explicitly targets it;
- debounce expensive live searches;
- show progress for slow AJAX actions;
- prevent duplicate submissions;
- keep server-side permission/validation even if client validation exists;
- restore controls after AJAX error;
- return useful error messages.

## Repeated frontend module instances

Assume an OpenCart layout can render the same module more than once.

Avoid:

- fixed IDs such as `#module-button` on every instance;
- singleton global state unless intentionally shared;
- CSS selectors that leak into another module instance.

Prefer a unique instance wrapper/data attribute and initialize within that wrapper.

## Theme compatibility

Catalog frontend styling must coexist with the active theme.

Prefer:

- namespaced module classes;
- low-specificity selectors where possible;
- explicit module wrapper;
- no global `button`, `input`, `.container`, `.row`, `h3` overrides;
- no blanket Bootstrap replacements;
- no `!important` flood.

When Journal3 or another theme requires compatibility code, isolate that compatibility from the module's base UI.

## Admin mobile/responsive behavior

Admin mobile support does not mean every desktop data grid must be identical on a phone.

Choose the appropriate transformation:

```text
wide settings form → one-column sections
large table → horizontal scroll or responsive cards
sidebar filters → collapsible filter drawer/section
bulk toolbar → sticky/compact action area
many tabs → scrollable tabs or select/accordion when justified
```

Preserve functionality first, then visual density.

## UI validation checklist

Before completion verify the relevant subset:

- frontend at narrow mobile width;
- frontend at tablet/desktop width;
- admin at laptop width;
- admin at narrow/tablet width when practical;
- no horizontal overflow caused by the module;
- all primary actions are reachable by touch;
- loading/error/empty/disabled states;
- keyboard focus for important controls;
- preset switching;
- `Custom` theme save/reload;
- invalid custom colors/default fallback;
- contrast/readability;
- multiple module instances on one page if supported;
- active theme compatibility;
- Journal3/custom-theme compatibility if the target project uses it;
- translations do not break layout with longer strings;
- no global CSS leakage;
- no JS selector collisions.

## Definition of done

A visible OpenCart module is not finished merely because it renders.

It is done when:

- layout is responsive for the target contexts;
- frontend is mobile-first;
- admin workflow is clear and modern enough for the task;
- themeable colors are centralized instead of hard-coded;
- presets and custom appearance work when the module's UI warrants them;
- states/accessibility are handled;
- styles and scripts are namespaced;
- target theme/admin integrations were tested or explicitly reported as unverified.
