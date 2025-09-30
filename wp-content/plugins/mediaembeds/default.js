/*
console.log(window.location);
console.log(window.location.href);
console.log(window.location.hostname);
console.log(window.location.pathname);
console.log(window.location.origin);

*/
//console.log(location.pathname.match(/^(\/.*)?(\/((.*)\.[^\/.]+))$/));
var req = location.pathname.match(/^(\/.*)?(\/((.*)\.[^\/.]+))$/);
var uri = req ? (req[1] ? req[1] : '/') : location.pathname;
var view = req ? req[4] :'default';


$(document).ready(function () {
	request_info();
});

function request_info() {
	var request_info = 'pathname: ' + window.location.pathname + '\n';
	request_info += 'uri: '+uri+'\nview: '+view+'\n';
	$('#request_info').text(request_info);
};
