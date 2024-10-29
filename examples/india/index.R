# Load necessary libraries
library(leaflet)
library(htmlwidgets)

# Create a leaflet map
india_map <- leaflet() %>%
  addTiles() %>%
  setView(lng = 78.9629, lat = 20.5937, zoom = 5)  
# Sample data with top cities of India
cities_data <- data.frame(
  city = c("Mumbai", "Delhi", "Bangalore", "Hyderabad", "Chennai", "Kolkata"),
  lat = c(19.0760, 28.6139, 12.9716, 17.3850, 13.0827, 22.5726),
  lng = c(72.8777, 77.2090, 77.5946, 78.4867, 80.2707, 88.3639),
  population = c(12442373, 11034555, 8443675, 6772291, 4681087, 4486679)
)

# Add circle markers for each city
india_map <- india_map %>%
  addCircleMarkers(
    data = cities_data,
    lng = ~lng,
    lat = ~lat,
    radius = ~sqrt(population) * 0.01,
    color = "orange",
    fillOpacity = 0.7,
    popup = ~paste("City: ", city, "<br>Population: ", population)
  )

# Display the map
# india_map

# Save the map as an HTML widget
saveWidget(india_map, file = "index.html")
