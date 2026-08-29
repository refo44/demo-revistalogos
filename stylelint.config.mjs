/** @type {import('stylelint').Config} */
export default {
  extends: ["stylelint-config-standard"],
  rules: {
    "alpha-value-notation": null,
    "color-function-alias-notation": null,
    "color-function-notation": null,
    "comment-empty-line-before": null,
    "declaration-block-no-redundant-longhand-properties": null,
    "declaration-property-value-no-unknown": null,
    "media-feature-range-notation": null,
    "no-descending-specificity": null,
    "property-no-deprecated": null,
    "selector-max-id": 0,
    "max-nesting-depth": 3,
    "selector-class-pattern": [
      "^(?:is-[a-z0-9]+(?:-[a-z0-9]+)*|has-[a-z0-9]+(?:-[a-z0-9]+)*|[a-z][a-z0-9]*(?:-[a-z0-9]+)*(?:__(?:[a-z0-9]+(?:-[a-z0-9]+)*))?(?:--(?:[a-z0-9]+(?:-[a-z0-9]+)*))?)$",
      {
        message:
          "Expected class selectors to use kebab-case with optional BEM segments."
      }
    ]
  },
  // Override globs are resolved relative to this config file. A bare
  // `assets/css/**` matched nothing, so `color-no-hex` never ran and
  // tokens.css lost its intended exemption. The paths are spelled out rather
  // than reached with `**/`, which would also pull in unrelated trees such as
  // `wordpress/wp-content/plugins/revistalogos-core/assets/css/` — admin CSS
  // that legitimately uses hex and is outside the design-token system. These
  // two globs mirror the `lint:css` script exactly.
  overrides: [
    {
      files: [
        "static/assets/css/**/*.css",
        "wordpress/wp-content/themes/revistalogos/assets/css/**/*.css"
      ],
      rules: {
        "color-no-hex": true
      }
    },
    {
      // tokens.css is where the raw palette and the system font stacks are
      // declared, so it keeps its hex literals and the vendor casing of font
      // names (`BlinkMacSystemFont`, `Georgia`, …). Listed last so it wins.
      files: [
        "static/assets/css/tokens.css",
        "wordpress/wp-content/themes/revistalogos/assets/css/tokens.css"
      ],
      rules: {
        "color-hex-length": null,
        "color-no-hex": null,
        "value-keyword-case": null
      }
    }
  ]
};
