library(terra)
library(leaflet)
library(leaflet.extras)
require(sf)
library(htmlwidgets)

 # you can download by accessing https://rstudio.github.io/leaflet/nc/oisst-sst.nc
 r <- rast("https://rstudio.github.io/leaflet/nc/oisst-sst.nc")
 pal <- colorNumeric(c("#0C2C84", "#41B6C4", "#FFFFCC"), values(r),
                     na.color = "transparent")
 
 m <- leaflet() %>% addTiles() %>%
      addRasterImage(r, colors = pal, opacity = 0.8) %>%
      addLegend(pal = pal, values = values(r),
      title = "Surface temp")%>%

 # Add Layer controls
	addMeasurePathToolbar(options = measurePathOptions(imperial = FALSE, showDistances = TRUE)) %>% 
  	addDrawToolbar(
	targetGroup = "draws",
	editOptions = editToolbarOptions(
        selectedPathOptions = selectedPathOptions()))


 saveWidget(m, file = "index.html") 