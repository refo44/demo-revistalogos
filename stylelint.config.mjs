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
  // Override globs are resolved relative to this config file, so they need a
  // leading `**/` to reach the two CSS trees (`static/assets/css/**` and
  // `wordpress/wp-content/themes/revistalogos/assets/css/**`). Without it
  // neither override matched anything: `color-no-hex` never ran, and
  // tokens.css lost its intended exemption.
  overrides: [
    {
      files: ["**/assets/css/**/*.css"],
      rules: {
        "color-no-hex": true
      }
    },
    {
      // tokens.css is where the raw palette and the system font stacks are
      // declared, so it keeps its hex literals and the vendor casing of font
      // names (`BlinkMacSystemFont`, `Georgia`, …). Listed last so it wins.
      files: ["**/assets/css/tokens.css"],
      rules: {
        "color-hex-length": null,
        "color-no-hex": null,
        "value-keyword-case": null
      }
    }
  ]
};
