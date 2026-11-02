/**
 * Theme blocks in the Site Editor (sin build tools). All are dynamic:
 * the canvas shows the server render for the context post (template
 * preview), save stores only the block comment.
 */

(function (blocks, blockEditor, element, ServerSideRender) {
  "use strict";

  const el = element.createElement;

  [
    "revistalogos/site-header",
    "revistalogos/site-footer",
    "revistalogos/front-hero",
    "revistalogos/front-sidebar",
    "revistalogos/page-header",
    "revistalogos/page-aside",
    "revistalogos/contact-form",
  ].forEach(function (name) {
    blocks.registerBlockType(name, {
      edit: function (props) {
        const postId = props.context && props.context.postId;

        return el(
          "div",
          blockEditor.useBlockProps(),
          el(ServerSideRender, {
            block: name,
            attributes: props.attributes,
            urlQueryArgs: postId ? { post_id: postId } : {},
          })
        );
      },
      save: function () {
        return null;
      },
    });
  });
})(window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.serverSideRender);
