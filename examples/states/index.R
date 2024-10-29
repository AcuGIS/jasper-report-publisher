library(leaflet)
library(leaflet.extras)
library(RPostgreSQL)
require(sf)
library(htmlwidgets)

conn <- RPostgreSQL::dbConnect("PostgreSQL", host = "ras.acugis.com",
dbname = "b1chicagotvguide_rdb", user = "xxx", password = "xxx")

query <- paste('SELECT * FROM "states";')

states <- st_read(conn, query = query)
states <- st_transform(states, sp::CRS("+proj=longlat +datum=WGS84"))

# print(data)

m <- leaflet() %>%
	# you can see a global map appears and all seven continents shown twice in one view
	addTiles(group="OpenStreetMap") %>%
	addProviderTiles(providers$Esri.WorldImagery, group = "Esri World Imagery") %>%
	addPolygons(data = st_zm(states), fillColor = "#7FFFD4", weight = 3, fillOpacity = 0.5,
							popup = paste0("State name: ", states$state_name, "<br>",
					                   "State code: ", states$state_code, "<br>",
					                   "Program: ", 	 states$program, "<br>"
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