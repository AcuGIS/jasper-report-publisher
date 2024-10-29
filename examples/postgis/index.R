library(leaflet)
library(leaflet.extras)
library(RPostgreSQL)
require(sf)
library(htmlwidgets)

	conn <- RPostgreSQL::dbConnect("PostgreSQL", host = "localhost",
	dbname = "rdemo", user = "xxxx", password = "xxxx")

	query <- paste('SELECT * FROM "neighborhoods";')

	neighborhoods <- st_read(conn, query = query)
	neighborhoods <- st_transform(neighborhoods, sp::CRS("+proj=longlat +datum=WGS84"))

# print(data)

 m <- leaflet() %>%
	addTiles(group="OpenStreetMap") %>%
	addProviderTiles(providers$Esri.WorldImagery, group = "Esri World Imagery") %>%
	addPolygons(data = st_zm(neighborhoods), fillColor = "#666", weight = 3, fillOpacity = 0.5,
		popup = paste0("Name: ", neighborhoods$pri_neigh, "<br>",
	        "Secondary: ", neighborhoods$sec_neigh, "<br>",
	        "Length: ", 	 neighborhoods$shape_len, "<br>"
				      )
			) %>%
	# Add Layer controls
  	addLayersControl(baseGroups=c("OpenStreetMap", "Esri World Imagery"), options=layersControlOptions(collapsed=FALSE)) %>%
	addMeasurePathToolbar(options = measurePathOptions(imperial = FALSE, showDistances = TRUE)) %>% 
	addDrawToolbar(
    	targetGroup = "draws",
    	editOptions = editToolbarOptions(
        selectedPathOptions = selectedPathOptions()))

# Save the map as an HTML widget
 saveWidget(m, file = "index.html")
