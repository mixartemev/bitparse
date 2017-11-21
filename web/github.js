var page = require('webpage').create();
page.open('http://coin.tbcgroupm.ru/bitparse/web/?var=1', function() {
    page.render('github.png');
    phantom.exit();
});