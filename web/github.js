var page = require('webpage').create();
page.open('http://coin.tmtbase.io/?var=1', function() {
    page.render('coin.jpg', {format: 'jpeg', quality: '100'});
    phantom.exit();
});