(function() {
  CodeMirror.commands.newlineAndIndentContinueMarkdownList = function(cm) {
    var pos = cm.getCursor(), token = cm.getTokenAt(pos);
    var mode = CodeMirror.innerMode(cm.getMode(), token.state).mode;
    var space;
    if (token.className == "string") {
      var full = cm.getRange({line: pos.line, ch: 0}, {line: pos.line, ch: token.end});
      var listStart = /\-|\d+\./, listContinue;
      if (token.string.search(listStart) == 0) {
        var reg = /^[\W]*(\d+)\./g;
        var matches = reg.exec(full);
        if(matches)
          listContinue = (parseInt(matches[1]) + 1) + ". ";
        else
          listContinue = "- ";
        space = full.slice(0, token.start);
        if (!/^\s*$/.test(space)) {
          space = "";
          for (var i = 0; i < token.start; ++i) space += " ";
        }
      }
    } else if(token.className == "quote"){
      space = "";
      listContinue = '> ';
    }

    if (space != null)
      cm.replaceSelection("\n" + space + listContinue, "end");
    else
      cm.execCommand("newlineAndIndent");
  };
})();