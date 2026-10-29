/**
 * Site header and footer blocks in the Site Editor (sin build tools).
 * Both are dynamic: the canvas shows the server render, save stores
 * only the block comment.
 */

(function (blocks, blockEditor, element, ServerSideRender) {
  "use strict";

  const el = element.createElement;

  ["revistalogos/site-header", "revistalogos/site-footer"].forEach(function (name) {
    blocks.registerBlockType(name, {
      edit: function () {
        return el("div", blockEditor.useBlockProps(), el(ServerSideRender, { block: name }));
      },
      save: function () {
        return null;
      },
    });
  });
})(window.wp.blocks, window.wp.blockEditor, window.wp.element, window.wp.serverSideRender);
