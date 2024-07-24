var system = require('system');
var page = require('webpage').create();

if (system.args.length !== 3) {
  console.log('Usage: convert.js <input file> <output file>');
  phantom.exit(1);
}

var input = system.args[1];
var output = system.args[2];

page.settings.localToRemoteUrlAccessEnabled = true;
page.open(input, function (status) {
  if (status !== 'success') {
    console.log('Unable to load the file: ' + input);
    phantom.exit(1);
  }

  page.paperSize = {
    format: 'A4',
    orientation: 'landscape',
    margin: '0cm'
  };

  setTimeout(function() {
      page.render(output);
      phantom.exit();
  }, 50);
});