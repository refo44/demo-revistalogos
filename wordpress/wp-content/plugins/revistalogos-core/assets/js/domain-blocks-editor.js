/**
 * Domain blocks in the Site Editor (sin build tools). All are dynamic:
 * the canvas shows the server render for the context post (Query Loop
 * or template preview), save stores only the block comment.
 */

(function (blocks, blockEditor, element, ServerSideRender) {
  "use strict";

  const el = element.createElement;

  [
    "revistalogos/current-issue",
    "revistalogos/issue-card",
    "revistalogos/issue-toc",
    "revistalogos/article-card",
    "revistalogos/article-metadata",
    "revistalogos/article-citation",
    "revistalogos/author-card",
    "revistalogos/recent-articles",
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
