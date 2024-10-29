library(leaflet)
library(leaflet.extras)
require(sf)
library(htmlwidgets)


# From https://leafletjs.com/examples/choropleth/us-states.js
	states <- sf::read_sf("https://rstudio.github.io/leaflet/json/us-states.geojson")

	bins <- c(0, 10, 20, 50, 100, 200, 500, 1000, Inf)
	pal <- colorBin("YlOrRd", domain = states$density, bins = bins)

	labels <- sprintf(
	  "<strong>%s</strong><br/>%g people / mi<sup>2</sup>",
	  states$name, states$density
	) %>% lapply(htmltools::HTML)

 m <- leaflet(states) %>%
  	setView(-96, 37.8, 4) %>%
  	addPolygons(
	fillColor = ~pal(density),
    	weight = 2,
    	opacity = 1,
    	color = "white",
    	dashArray = "3",
    	fillOpacity = 0.7,
    	highlightOptions = highlightOptions(
      	weight = 5,
      	color = "#666",
      	dashArray = "",
      	fillOpacity = 0.7,
      	bringToFront = TRUE),
    	label = labels,
    	labelOptions = labelOptions(
      	style = list("font-weight" = "normal", padding = "3px 8px"),
      	textsize = "15px",
      	direction = "auto")) %>%
  addLegend(pal = pal, values = ~density, opacity = 0.7, title = NULL,
    	position = "bottomright") %>%
  addTiles(group="OpenStreetMap") %>%
  addProviderTiles(providers$Esri.WorldImagery, group = "Esri World Imagery") %>%
  addLayersControl(baseGroups=c("OpenStreetMap", "Esri World Imagery"), options=layersControlOptions(collapsed=FALSE)) %>%
  addMeasurePathToolbar(options = measurePathOptions(imperial = FALSE, showDistances = TRUE)) %>% 
  addDrawToolbar(
  	targetGroup = "draws",
	editOptions = editToolbarOptions(
        selectedPathOptions = selectedPathOptions()))

 saveWidget(m, file = "index.html") 
