L.control.browserPrint({
title: 'Just print me!',
documentTitle: 'Map printed using leaflet.browser.print plugin',
printLayer: L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
subdomains: 'abcd',
minZoom: 1,
maxZoom: 16,
ext: 'png'
}),
closePopupsOnPrint: false,
printModes: [
            L.BrowserPrint.Mode.Landscape(),
            "Portrait",
            L.BrowserPrint.Mode.Auto("B4",{title: "Auto"}),
            L.BrowserPrint.Mode.Custom("B5",{title:"Select area"})
],
manualMode: false
}).addTo(map);