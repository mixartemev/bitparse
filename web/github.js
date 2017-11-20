var page = require('webpage').create();
page.open('http://bitparse/index.php?r=site&var=1', function() {
    page.render('github.png');
    phantom.exit();
});