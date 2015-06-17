var page = require('webpage').create(),
	system = require('system');

page.viewportSize = { width: 1024, height: 768 };
page.open(system.args[1], function(status) {
	if('success' === status) {
		page.render(system.args[2]);

		console.log(1);
	} else {
		console.log(0);
	}

	phantom.exit();
});