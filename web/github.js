var page = require('webpage').create();
page.open('http://coin.tbcgroupm.ru/bitparse/web/?var=1', function() {
    page.render('coin.jpg', {format: 'jpeg', quality: '100'});
    phantom.exit();
});