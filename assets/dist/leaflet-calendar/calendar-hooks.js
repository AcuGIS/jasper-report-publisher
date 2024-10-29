function onSelectDate(k,v) {
	var params = new URLSearchParams(location.search);
	params.set(k, v);
	window.location.search = params.toString();
}