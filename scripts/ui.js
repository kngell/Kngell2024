// ui.js
(function() {
  var currentTab = 'json';
  var tokens = {};
  var output = document.getElementById('output');

  function toSCSS(obj, indent) {
    indent = indent || '';
    if (typeof obj !== 'object' || obj === null) return obj;
    var out = '(
';
    for (var k in obj) {
      out += indent + '  ' + k + ': ';
      if (typeof obj[k] === 'object') {
        out += toSCSS(obj[k], indent + '  ');
      } else {
        out += obj[k];
      }
      out += ',\n';
    }
    out += indent + ')';
    return out;
  }

  function showTab(tab) {
    currentTab = tab;
    document.getElementById('tab-json').classList.toggle('active', tab === 'json');
    document.getElementById('tab-scss').classList.toggle('active', tab === 'scss');
    output.value = tab === 'json' ? JSON.stringify(tokens, null, 2) : toSCSS(tokens);
  }

  window.onmessage = function(event) {
    var msg = event.data.pluginMessage;
    if (msg && msg.type === 'tokens') {
      tokens = msg.tokens;
      showTab(currentTab);
    }
  };

  document.getElementById('generate').onclick = function() {
    var caseType = document.querySelector('input[name="case"]:checked').value;
    parent.postMessage({ pluginMessage: { type: 'generate', caseType: caseType } }, '*');
  };
  document.getElementById('tab-json').onclick = function() { showTab('json'); };
  document.getElementById('tab-scss').onclick = function() { showTab('scss'); };
  document.getElementById('copy').onclick = function() {
    output.select();
    document.execCommand('copy');
  };
  document.getElementById('download').onclick = function() {
    var blob = new Blob([output.value], { type: 'text/plain' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = currentTab === 'json' ? 'tokens.json' : 'tokens.scss';
    a.click();
  };
})();
